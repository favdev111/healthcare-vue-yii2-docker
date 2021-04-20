<?php

namespace common\models\form;

use common\components\Formatter;
use common\models\AccountTerms;
use common\components\ZipCodeHelper;
use modules\account\models\Account;
use modules\account\Module;
use modules\payment\models\Transaction;
use UrbanIndo\Yii2\Queue\Job;
use yii\base\Model;
use yii\imagine\Image;

/**
 * Class SignatureForm
 * @property Account $client
 * @property Transaction $firstPayment
 * @property integer $clientId
 * @property-read float $amountPaid
 * @property-read string $date
 * @property-read string $clientAddress
 * @property-read string $clientFullName
 * @property-read string $ip
 * @package common\models\form
 */
class SignatureForm extends Model
{
    public $signature;
    public $clientId;
    protected $userIp;
    protected $signaturePath;
    protected $clientAccount;
    protected $firstTransaction;
    const FORM_FIELD_SIGNATURE = 'signature';
    public function rules()
    {
        return [
            [['clientId', 'signature'] , 'required'],
            [['signature'] , 'string'],
            [['clientId'], 'exist', 'skipOnError' => true, 'targetClass' => Account::class, 'targetAttribute' => ['clientId' => 'id']],
        ];
    }

    /**
     * @return Account
     */
    public function getClient(): Account
    {
        if (empty($this->clientAccount)) {
            /**
             * @var Account $clientAccount
             */
            $clientAccount = Account::find()->andWhere([Account::tableName() . '.id' => $this->clientId])->with('profile')->limit(1)->one();
            $this->clientAccount = $clientAccount;
        }
        return $this->clientAccount;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        if (empty($this->userIp)) {
            $this->userIp = ZipCodeHelper::getUserIpOrFromCookie();
        }
        return $this->userIp;
    }

    /**
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getFirstPayment()
    {
        if (empty($this->firstTransaction)) {
            $transaction = Transaction::find()
                ->andWhere(['studentId' => $this->client->id])
                ->andWhere(['status' => Transaction::STATUS_SUCCESS])
                ->limit(1)
                ->orderBy(['createdAt' => SORT_ASC])->one();
            $this->firstTransaction = $transaction ?? null;
        }
        return $this->firstTransaction;
    }

    /**
     * @return float
     */
    public function getAmountPaid(): float
    {
        return $this->firstPayment->amount ?? 0;
    }


    public function getDate(): string
    {
        /**
         * @var $formatter Formatter
         */
        $formatter = \Yii::$app->formatter;
        return !empty($this->firstPayment) ? $formatter->asDateWithSlashes($this->firstPayment->createdAt) : date($formatter->dateWithSlashesPhp);
    }

    /**
     * @return string
     */
    public function getClientAddress(): string
    {
        return $this->client->profile->address ?? '';
    }

    /**
     * @return string
     */
    public function getClientFullName(): string
    {
        return $this->client->profile->fullName();
    }

    public function processSignature(): bool
    {
        try {
            if ($this->loadPicture() && $this->savePicture() && $this->postTask() && $this->signUpTerms()) {
                return true;
            } else {
                $this->addError('signature', 'Something went wrong. Please contact us for more information.');
                return false;
            }
        } catch (\Throwable $exception) {
            $this->addError('signature', 'Something went wrong. Please contact us for more information.');
            \Yii::error($exception->getMessage() .  "\n" . $exception->getTraceAsString(), 'terms');
            return false;
        }
    }

    protected function loadPicture(): bool
    {
        $signature = $this->signature;
        try {
            list($type, $data) = explode(';', $signature);

            if ('data:image/png' !== $type) {
                $this->addError(static::FORM_FIELD_SIGNATURE, 'Invalid format');
                return false;
            }
            list($text, $data) = explode(',', $data);
            if ('base64' !== $text) {
                $this->addError(static::FORM_FIELD_SIGNATURE, 'Invalid format');
                return false;
            }

            $data = base64_decode($data);
            $imagine = Image::getImagine();

            $this->signature = $imagine->load($data);
            return true;
        } catch (\Exception $ex) {
            $this->addError(static::FORM_FIELD_SIGNATURE, 'Signature loading fail');
        }
        return false;
    }

    protected function savePicture(): bool
    {
        /**
         * @var Module $accountModule
         */
        $accountModule = \Yii::$app->getModule('account');
        $name = $this->clientId . '_signature';
        $this->signaturePath = $accountModule->pathToSignatures . $name . '.png';
        $this->signature->save($this->signaturePath);
        return true;
    }

    protected function postTask(): bool
    {
        $task = new Job([
            'route' => 'signature/create-pdf',
            'data' => [
                'signaturePath' => $this->signaturePath,
                'clientId' => $this->clientId,
                'ip' => $this->ip
            ]
        ]);
        \Yii::$app->queue->post($task);
        return true;
    }

    protected function signUpTerms(): bool
    {
        $term = $this->getClient()->terms;
        if (empty($term)) {
            $term = new AccountTerms(['accountId' => $this->getClient()->id]);
        }
        $term->termsSigned = true;
        return $term->save(false);
    }
}

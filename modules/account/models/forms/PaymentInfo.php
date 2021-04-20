<?php

namespace modules\account\models\forms;

use common\components\UploadedFile;
use common\models\Zipcode;
use modules\account\models\Account;
use modules\account\models\PaymentAccount;
use modules\payment\models\BankAccount;
use Yii;
use yii\base\Model;

/**
 * PaymentInfo is the model behind the login form.
 *
 * @property BankAccount $bankAccount
 * @property PaymentAccount $paymentAccount
 * @property Account $userAccount
 */
class PaymentInfo extends Model
{

    const SCENARIO_EXTRA_DATA_DOCUMENT = 'extra-data-document';
    const SCENARIO_EXTRA_DATA = 'extra-data';

    public $bankToken;
    public $ssn;
    public $piiToken;
    public $document;

    public $city;
    public $state;
    public $zipCode;
    public $addressLine1;
    public $placeId;
    public $companyId;

    protected $currentBankAccount;
    protected $currentPaymentAccount;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['bankToken'], 'required'],
            'placeId' => [
                ['placeId'],
                'required',
                'when' => function () {
                    $account = $this->userAccount;
                    return !$account->paymentAccount
                        || !$account->paymentAccount->verified;
                }
            ],
            [['ssn'], 'required', 'when' => function () {
                $account = $this->userAccount;
                return !$account->paymentAccount
                    || !$account->paymentAccount->verified;
            }
            ],
            [['city', 'state', 'zipCode', 'addressLine1'], 'string'],
            [['piiToken'], 'required', 'on' => 'extra-data'],
            [['document'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'maxSize' => 8192000, 'tooBig' => 'Max file size is 8MB', 'on' => 'extra-data-document'],
            [['zipCode'], 'exist', 'skipOnError' => true, 'targetClass' => Zipcode::className(), 'targetAttribute' => ['zipCode' => 'code']],
        ];
    }

    public function load($data, $formName = null)
    {
        // In case it's Document upload - get Uploaded file first
        $this->document = UploadedFile::getInstanceByName('document');
        if ($this->document === null) {
            $this->document = UploadedFile::getInstance($this, 'document');
        }
        if ($this->scenario === self::SCENARIO_DEFAULT) {
            // In case no non-default scenario provided - trying to guess appropriate scenario
            if (!empty($data['piiToken'])) {
                $this->scenario = self::SCENARIO_EXTRA_DATA;
            }
            if ($this->document instanceof UploadedFile) {
                $this->scenario = self::SCENARIO_EXTRA_DATA_DOCUMENT;
            }
        }
        if (!parent::load($data, $formName)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'bankName' => 'Bank Name',
            'accountNumber' => 'Account Number',
            'routingNubmer' => 'Routing Number',
            'city' => 'City',
            'state' => 'State',
            'zipCode' => 'Zip Code',
            'addressLine1' => 'Address Line 1',
            'ssn' => 'Social Security Number',
            'personal_id_number' => 'Full Social Security Number (9 digits)',
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['extra-data'] = ['piiToken'];
        $scenarios['extra-data-document'] = ['document'];
        return $scenarios;
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $account = $this->userAccount;

        if (!$account->paymentAccount) {
            // Create payment account if none exist
            if (!Yii::$app->payment->createAccount($this->userAccount->id, $error)) {
                $this->addError('', 'Failed to create payment account. ' . $error . ' Please try again or contact us.');
                return false;
            }
            $account->refresh();
        }

        // Fill payment account with data if needed
        if (!Yii::$app->payment->fillStripeAccountWithData($this->userAccount->id, $this, $error)) {
            $this->addError('', 'Failed to update your billing address. ' . $error . ' Please try again or contact us.');
            return false;
        }
        $account->paymentAccount->verified = true;
        if (!$account->paymentAccount->save()) {
            $this->addErrors($account->paymentAccount->getErrors());
            return false;
        }

        if ($this->bankToken) {
            $this->currentBankAccount = Yii::$app->payment->createBankAccount($this->bankToken);
            if ($this->currentBankAccount->hasErrors()) {
                Yii::error('Failed to create bank account. Errors: ' . json_encode($this->currentBankAccount->getErrors()));
                $this->addErrors($this->currentBankAccount->getErrors());
                return false;
            }
        }

        if ($this->document) {
            $this->currentPaymentAccount = Yii::$app->payment->uploadDocument($this->document);
            if ($this->currentPaymentAccount->hasErrors()) {
                Yii::error('Failed to upload document. Errors: ' . json_encode($this->currentPaymentAccount->getErrors()));
                $this->addError('', 'Failed to upload document. Please try again or contact us.');
                return false;
            }
        }

        if ($this->piiToken) {
            if (!Yii::$app->payment->setPersonalNumber($this->piiToken, $error)) {
                $this->addError('', 'Failed to save SSN. ' . $error . ' Please try again or contact us.');
                return false;
            }
            $this->currentPaymentAccount = $account->paymentAccount;
        }

        return true;
    }

    protected function isAddressInfoProvided()
    {
        return $this->placeId;
    }

    public function getBankAccount()
    {
        return $this->currentBankAccount;
    }

    public function getPaymentAccount()
    {
        return $this->currentPaymentAccount;
    }

    public static function getExtraVerifyData()
    {
        // This part is required for proper testing
        // (anyway it will not create any bugs on live environment since Stripe allows adding document and full SSN in advance)
        // TODO: Add an option to force ssn and document on b2b side
        if (!empty($_COOKIE['document']) || !empty($_COOKIE['personal_id_number'])) {
            $value = (!empty($_COOKIE['document']) ? 'payment.document' : 'payment.personal_id_number');
            // TODO: Move this part to some kind of helper for future use.
            $secret = (!empty($_COOKIE['document']) ? $_COOKIE['document'] : $_COOKIE['personal_id_number']);
            if ($secret === 'icemint') {
                $extraStripeData = new \stdClass();
                $extraStripeData->fields_needed = [
                    $value
                ];
                return $extraStripeData;
            }
        }

        return Yii::$app->payment->getExtraVerifyData();
    }

    public static function getExtraVerifyDataFields()
    {
        $result = [];
        $extraStripeData = self::getExtraVerifyData();
        foreach ($extraStripeData->fields_needed as $field) {
            if (is_string($field)) {
                $fieldArray = explode('.', $field);
                $fieldName = array_pop($fieldArray);
                $result[] = $fieldName;
            } elseif (!empty($field['requirement']) && is_array($field)) {
                $result[] = $field['requirement'];
            }
        }
        return $result;
    }
}

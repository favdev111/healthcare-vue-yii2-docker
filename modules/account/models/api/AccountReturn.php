<?php

namespace modules\account\models\api;

use common\components\Formatter;
use common\components\validators\JobHireClientValidator;
use modules\account\helpers\Timezone;
use modules\account\models\AccountNote;
use modules\account\models\RematchJobHire;

class AccountReturn extends \modules\account\models\AccountReturn
{
    const JOB_HIRE_REQUIRED_MESSAGE = 'Please select at least one relationship';

    //for rematched job hires
    public $jobHiresIds;
    public $tutorsNotificationsEnabled = true;

    protected static $accountClass = AccountClient::class;
    protected static $newFlagColor;


    public static $reasonDescription = [];

    public static $changeLogDescription = '';
    /**
     * New item can be created only with active reason
     * @var array $activeReasons
     */
    public static $activeReasons = [];

    /**
     * @param int $reasonCode
     * @return bool
     */
    public static function isReasonActive(int $reasonCode): bool
    {
        return in_array($reasonCode, static::$activeReasons);
    }

    public static function getInstance(array $attributes)
    {
        if (empty($attributes['type'])) {
            return false;
        }
        switch ($attributes['type']) {
            case self::TYPE_REMATCH:
                $model = new ClientRematch();
                break;
            case self::TYPE_REFUND:
                $model = new ClientRefund();
                break;
            default:
                $model = new static();
                break;
        }
        $model->load($attributes, '');
        return $model;
    }


    public function extraFields()
    {
        return array_merge(parent::extraFields(), [
            'rematchJobHires'
        ]);
    }

    public function rules()
    {
        /**
         * @var Formatter $formatter
         */
        $formatter = \Yii::$app->formatter;
        return array_merge(parent::rules(), [
            [['startDate'], 'required'],
            [['jobHiresIds'], 'required', 'message' => static::JOB_HIRE_REQUIRED_MESSAGE],
            ['reasonCode', 'in', 'range' => static::$activeReasons],
            [['jobHiresIds'], 'each', 'rule' => [JobHireClientValidator::class]],
            [['startDate'], 'date', 'type' => 'datetime', 'min' => date(static::getIncomingDateFormat()), 'format' => 'php:' . static::getIncomingDateFormat(), 'timestampAttribute' => 'startDate', 'timestampAttributeFormat' => 'php:' . static::getInternalDateFormat()],
            [['startDate'], 'filter', 'skipOnEmpty' => true, 'filter' => function ($value) use ($formatter) {
                return Timezone::staticConvertToServerTimeZone($value, $formatter->MYSQL_DATE . ' ' . $formatter->MIDDAY_HOUR, 12);
            }
            ],
        ]);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $this->saveClientNote();
            $this->changeFlag();
        }
    }

    protected function saveClientNote()
    {
        $note = new AccountNote(['accountId' => $this->accountId]);
        $note->content = $this->getNote();
        return $note->save(false);
    }

    protected function changeFlag(): bool
    {
        if (static::$newFlagColor) {
            $account = $this->account;
            $account->flag = static::$newFlagColor;
            $account->changeLogComment = static::$changeLogDescription ?? '';
            return $account->save(false);
        }
        return false;
    }


    public function getNote(): string
    {
        return static::$reasonDescription[$this->reasonCode] ?? '';
    }

    protected function processJobHires()
    {
        $data = \modules\account\models\JobHire::find()
            ->select(['id', 'tutorId'])
            ->andWhere(['id' => $this->jobHiresIds])
            ->asArray()
            ->all();

        $processedTutorIds = [];
        foreach ($data as $item) {
            $model = new RematchJobHire([
                'accountReturnId' => $this->id,
                'jobHireId' => $item['id'],
                'notifyTutor' => $this->tutorsNotificationsEnabled && !in_array($item['tutorId'], $processedTutorIds)
            ]);
            $model->save(false);
            $processedTutorIds[] = $item['tutorId'];
        }
    }

    public function setClientStartDate()
    {
        $profile = $this->account->profile;
        $profile->startDate = $this->startDate;
        $profile->save(false);
    }
}

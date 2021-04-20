<?php

namespace modules\sms\models;

use common\helpers\QueueHelper;
use modules\account\models\Account;
use modules\sms\components\Sms;
use yii\base\Model;

/**
 * Class FollowUp
 * @property Account $account
 * @package modules\sms\models
 */
abstract class FollowUp extends Model
{
    /**
     * @var Sms $smsComponent
     */
    protected $smsComponent;
    protected $smsModel;
    protected $extraData;
    public $responseText;

    //should be overwrite
    const IS_USER_RESPONSE_EXPECTED = true;
    const SMS_TYPE = 0;

    const ANSWER_DEFAULT = 'Your response has been submitted. For any questions or concerns please call 855-661-3688.';

    public function init()
    {
        parent::init();
        $this->smsComponent = \Yii::$app->sms;
    }

    public static function selectSmsAnswer(\common\models\Sms $sms): string
    {
        $answer = '';
        if ($sms->isUserResponseUndefined()) {
            $answer = static::ANSWER_DEFAULT;
        }

        switch ($sms->type) {
            case \common\models\Sms::TYPE_FOLLOW_UP_HIRE_TO_TUTOR:
                $answer = static::ANSWER_DEFAULT;
                break;
        }
        return $answer;
    }

    //need implement in child classes
    abstract public function getComposedMessage();
    abstract public function getAccount();

    public function createSms(): bool
    {
        $account = $this->getAccount();
        $message = $this->getComposedMessage();
        try {
            $this->smsModel = $this->smsComponent->createSms(
                $message,
                $account->profile->phoneNumber,
                static::SMS_TYPE,
                $account->id,
                $this->extraData
            );
            return true;
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), 'sms');
            return false;
        }
    }

    public function getData(): array
    {
        return ['accountId' => $this->account->id];
    }

    /**
     * Start sending sms process
     */
    public function sendSms(): void
    {
        QueueHelper::sendSms($this->smsModel);
    }
}

<?php

namespace common\models;

use common\helpers\QueueHelper;
use modules\account\models\Account;
use Yii;
use yii\base\Exception;

/**
 * This is the model class for table "{{%sms}}".
 *
 * @property integer $id
 * @property string $twilioMessageSID
 * @property integer $accountId
 * @property integer $type
 * @property integer $status
 * @property string $phone
 * @property string $sentAt
 * @property string $createdAt
 * @property string $text
 * @property string $userResponse
 * @property integer $userResponseStatus
 * @property string $userResponseAt
 * @property integer $deliveryStatus
 * @property string $deliveryStatusUpdatedAt
 * @property array $extraData
 *
 * @property Account $account
 */
class Sms extends \yii\db\ActiveRecord
{
    const RESPONSE_DEFAULT = 'In order for us to help you, or if this is an urgent matter, please contact us directly at support@winitclinic.com. Thank you!';
    //values for type field
    const TYPE_FOLLOW_UP_HIRE_TO_TUTOR = 1;
    const TYPE_CONGRATULATION_MARKETING = 4;
    const TYPE_NEW_CHAT_MESSAGE = 5;
    const TYPE_DOWNLOAD_APP = 6;
    const TYPE_APPLIED_JOB = 7;
    const TYPE_STUDENT_REPLIED_JOB = 8;
    const TYPE_NEW_JOB_POSTED = 9;
    const TYPE_LEAD = 10;

    //values for status field
    const STATUS_WAITING_TO_SEND = 1;
    const STATUS_SENT_TO_TWILIO = 2;
    const STATUS_ADDED_TO_QUEUE = 3;

    //values for userResponseStatus (followup answer). Possible adding new statuses in future
    const RESPONSE_STATUS_YES = 1;
    const RESPONSE_STATUS_NO = 2;
    const RESPONSE_STATUS_NO_USER_RESPONSE = 3;
    //no "yes" and "no" words in response
    const RESPONSE_STATUS_NOTHING = 4;

    //values for deliveryStatus (from twilio)
    const DELIVERY_STATUS_QUEUED = 1;
    const DELIVERY_STATUS_FAILED = 2;
    const DELIVERY_STATUS_SENT = 3;
    const DELIVERY_STATUS_DELIVERED = 4;
    const DELIVERY_STATUS_UNDELIVERED = 5;

    public static $typesWithUserResponse = [Sms::TYPE_FOLLOW_UP_HIRE_TO_TUTOR];

    public static $twilioDeliveryStatusList = [
        'queued' => self::DELIVERY_STATUS_QUEUED,
        'failed' => self::DELIVERY_STATUS_FAILED,
        'sent' => self::DELIVERY_STATUS_SENT,
        'delivered' => self::DELIVERY_STATUS_DELIVERED,
        'undelivered' => self::DELIVERY_STATUS_UNDELIVERED,
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sms}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
                'updatedAtAttribute' => null,
            ],
        ]);
    }

    public static function getDeliveryStatus(string $twilioDeliveryStatus): int
    {
        if (empty(self::$twilioDeliveryStatusList[$twilioDeliveryStatus])) {
            throw new Exception('Invalid delivery status');
        }
        return self::$twilioDeliveryStatusList[$twilioDeliveryStatus];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'status', 'phone', 'text'], 'required'],
            [['accountId', 'type', 'status', 'userResponseStatus', 'deliveryStatus'], 'integer'],
            [['sentAt', 'createdAt', 'userResponseAt', 'deliveryStatusUpdatedAt'], 'safe'],
            [['text', 'userResponse'], 'string'],
            [['twilioMessageSID', 'phone'], 'string', 'max' => 255],
            [['accountId'], 'exist', 'skipOnError' => true, 'targetClass' => Account::class, 'targetAttribute' => ['accountId' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'twilioMessageSID' => 'Twilio Message Sid',
            'accountId' => 'Account ID',
            'type' => 'Type',
            'status' => 'Status',
            'phone' => 'Phone',
            'sentAt' => 'Sent At',
            'createdAt' => 'Created At',
            'text' => 'Text',
            'userResponse' => 'User Response',
            'userResponseStatus' => 'User Response Status',
            'userResponseAt' => 'User Response At',
            'deliveryStatus' => 'Delivery Status',
            'deliveryStatusUpdatedAt' => 'Delivery Status Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'accountId']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\SmsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\SmsQuery(get_called_class());
    }

    public function isFollowUp(): bool
    {
        return in_array($this->type, [static::TYPE_FOLLOW_UP_HIRE_TO_TUTOR]);
    }

    public function isUserResponseYes(): bool
    {
        return $this->userResponseStatus === static::RESPONSE_STATUS_YES;
    }

    public function isUserResponseNo(): bool
    {
        return $this->userResponseStatus === static::RESPONSE_STATUS_NO;
    }

    public function isUserResponseUndefined(): bool
    {
        return $this->userResponseStatus === static::RESPONSE_STATUS_NOTHING;
    }

    public function isNoUserResponse(): bool
    {
        return $this->userResponseStatus === static::RESPONSE_STATUS_NO_USER_RESPONSE;
    }

    public function getUserResponseStatusText(): string
    {
        switch ($this->userResponseStatus) {
            case static::RESPONSE_STATUS_YES:
                return 'YES';
                break;
            case static::RESPONSE_STATUS_NO:
                return 'NO';
                break;
            case static::RESPONSE_STATUS_NOTHING:
                return 'NOTHING';
                break;
            default:
                return '';
                break;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        QueueHelper::sendFollowUpReportEmail($this->id);
    }
}

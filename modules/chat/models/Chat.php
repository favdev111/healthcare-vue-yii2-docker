<?php

namespace modules\chat\models;

use modules\account\models\Account;
use modules\account\models\AccountWithDeleted;
use modules\chat\events\StatusChangeEvent;
use modules\chat\Module;
use modules\account\models\Role;
use modules\chat\models\query\ChatQuery;
use modules\payment\models\CardInfo;
use Yii;
use yii\base\Event;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%chat}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property string $login
 * @property string $password
 * @property integer $chatUserId
 * @property integer $status
 * @property integer $statusReason
 * @property integer $approved_at
 *
 * @property Account $account
 */
class Chat extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 10;
    const STATUS_SUSPICIOUS = 5;
    const STATUS_HOLD = 1;

    const STATUS_REASON_SPAM = 1;
    const STATUS_REASON_IP_BLOCK = 2;
    const STATUS_REASON_TIME_LIMIT = 3;

    public static $statusTexts = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_SUSPICIOUS => 'Suspicious',
        self::STATUS_HOLD => 'Hold',
    ];

    /**
     * @var array
     */
    public static $statusReasonTexts = [
        self::STATUS_REASON_IP_BLOCK => 'IP block',
        self::STATUS_REASON_SPAM => 'Spam',
        self::STATUS_REASON_TIME_LIMIT => 'Time Limit',
    ];

    public static function nonActiveStatuses()
    {
        return [
            self::STATUS_SUSPICIOUS,
            self::STATUS_HOLD,
        ];
    }

    /**
     * @return int|mixed
     */
    public function getStatusText()
    {
        return static::$statusTexts[$this->status] ?? $this->status;
    }

    /**
     * @return int|mixed
     */
    public function getStatusReasonText()
    {
        return static::$statusReasonTexts[$this->statusReason] ?? $this->statusReason;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%chat}}';
    }

    /**
     * @return array
     */
    public static function getUnverifiedStudents()
    {
        $query = static::find()
            ->select('chatUserId')
            ->andWhere(['roleId' => Role::ROLE_PATIENT]);

        return static::addWithoutCardQuery($query)
            ->asArray()
            ->column();
    }

    /***
     * @return ChatQuery
     */
    public static function find()
    {
        return new ChatQuery(static::class);
    }

    /**
     * @param $chatUserId
     * @return array|Account|null|\yii\db\ActiveRecord
     */
    public static function findAccountByChatId($chatUserId)
    {
        return Account::find()
            ->joinWith(['chat' => function ($query) use ($chatUserId) {
                /**
                 * @var ChatQuery $query
                 */
                $query->andWhereChatUserId($chatUserId);
            }
        ])
            ->one();
    }

    /**
     * @param ActiveQuery $query
     * @return ActiveQuery
     */
    protected static function addWithoutCardQuery(ActiveQuery $query)
    {
        return $query
            ->joinWith('account')
            ->joinWith('account.cardInfo')
            ->andWhere(['is', CardInfo::tableName() . '.id', null]);
    }

    /**
     * @param $message
     * @return mixed
     */
    public static function processUnverifiedMessage($message)
    {
        /**
         * @var $chatModule \modules\chat\Module
         */
        $chatModule = Yii::$app->getModule('chat');
        $mailDomains = $chatModule->getMailDomains();

        $message = preg_replace('/([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)/i', '*****', $message);
        $message = preg_replace('/(\d{3})\D?\D?(\d{3,4})\D?(\d{3,4})/i', '*****', $message);
        $message = str_replace($mailDomains, '*****', $message);

        return $message;
    }

    /**
     * @return boolean
     * @param $dialogUserIds
     * @return bool
     */
    public static function isDialogUnverified(array $dialogUserIds)
    {
        return static::addWithoutCardQuery(
            static::find()
                ->andWhere([Account::tableName() . '.roleId' => Role::ROLE_PATIENT])
                ->andWhere(['in' ,'chatUserId', $dialogUserIds])
        )->exists();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['login', 'password', 'chatUserId'], 'required'],
            [['chatUserId'], 'integer'],
            [['login', 'password'], 'string', 'max' => 255],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['statusReason', 'in', 'range' => [self::STATUS_REASON_SPAM, self::STATUS_REASON_IP_BLOCK, self::STATUS_REASON_TIME_LIMIT]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'accountId' => 'Account ID',
            'login' => 'Login',
            'password' => 'Password',
            'chatUserId' => 'Chat User ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(AccountWithDeleted::className(), ['id' => 'accountId']);
    }

    /**
     * @param $occupantsIds
     * @param null $userId
     * @param bool $exceptBlocked
     * @param bool $withoutRestrictions
     * @return bool|mixed
     */
    public static function getOpponentAccount(
        $occupantsIds,
        $userId = null,
        $exceptBlocked = true,
        $withoutRestrictions = false
    ) {
        if (!$userId) {
            $userId = Yii::$app->user->id;
        }
        if ($withoutRestrictions) {
            $accountQuery = Account::findWithoutRestrictions();
        } else {
            $accountQuery = Account::find();
        }

        $accountQuery
            ->joinWith('chat')
            ->andWhere(['chatUserId' => $occupantsIds])
            ->andWhere(['not', ['accountId' =>  $userId]]);

        if ($exceptBlocked) {
            $accountQuery->andNonSuspended();
        }

        $account = $accountQuery->one();
        if (!$account) {
            return false;
        }
        return $account;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (isset($changedAttributes['status'])) {
            $event = new StatusChangeEvent();
            $event->accountId = $this->accountId;
            $event->status = $this->status;
            if ($this->status === self::STATUS_SUSPICIOUS) {
                Event::trigger(Module::className(), Module::EVENT_CHAT_ACCOUNT_SUSPICIOUS, $event);
            }
            if ($this->status === self::STATUS_HOLD) {
                Event::trigger(Module::className(), Module::EVENT_CHAT_ACCOUNT_HOLD, $event);
            }
            // In case chat user is activated
            if (
                in_array($changedAttributes['status'], self::nonActiveStatuses())
                && $this->status === self::STATUS_ACTIVE
            ) {
                Event::trigger(Module::className(), Module::EVENT_CHAT_ACCOUNT_UNBLOCKED, $event);
            }
        }
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (!$insert && $this->isAttributeChanged('status') && $this->status === self::STATUS_ACTIVE) {
            // In case status changed to Active one (approved)
            $this->approved_at = time();
            $this->statusReason = null;
        }

        return true;
    }

    /**
     * @return bool Whether chat account is put on hold or not
     */
    public function isHold()
    {
        return $this->status === self::STATUS_HOLD;
    }

    /**
     * @return bool Whether chat account is put on hold or not
     */
    public function isIpBlockReason()
    {
        return $this->statusReason === self::STATUS_REASON_IP_BLOCK;
    }

    /**
     * @param $excludeHold boolean Whether to exclude hold accounts or not
     * @return bool is current account suspicious (or hold)
     */
    public function isSuspicious($excludeHold)
    {
        if ($excludeHold) {
            return $this->status === self::STATUS_SUSPICIOUS;
        }

        return in_array($this->status, [self::STATUS_SUSPICIOUS, self::STATUS_HOLD]);
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_ACTIVE && $this->approved_at;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = [
            'chatUserId',
            'login',
            'password',
            'status',
        ];
        return $fields;
    }

    /**
     * TODO need to configure urlManager (link to chat from method createAbsoluteUrl() shouldn't have slash in the end and should contains chatUserId)
     * @return string
     */
    public function getChatDialogUrl()
    {
        return Yii::$app->urlManager->createAbsoluteUrl('/messages') . '#' . $this->chatUserId;
    }
}

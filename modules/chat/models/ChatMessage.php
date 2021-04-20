<?php

namespace modules\chat\models;

use modules\account\models\Account;
use modules\account\models\AccountWithDeleted;
use modules\chat\models\query\ChatMessageQuery;
use Yii;
use yii\helpers\Console;

/**
 * This is the model class for table "chat_message".
 *
 * @property integer $id
 * @property string $_id
 * @property string $message
 * @property string $chat_dialog_id
 * @property integer $date_sent
 * @property integer $sender_id
 * @property integer $recipient_id
 * @property integer $isCompanyClient
 * @property boolean $isFirstMessage
 * @property boolean $recipientStatusRead
 * @property string $chatAttachmentUid
 * @property integer $messageType
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property Account $messageSender
 * @property Account $messageRecipient
 */
class ChatMessage extends \yii\db\ActiveRecord
{
    const TYPE_CHAT = 1;
    const TYPE_IMAGE = 2;
    const TYPE_VIDEO = 3;
    const TYPE_AUDIO = 4;
    const TYPE_OTHER = 6;

    protected static $modifiedDb;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'chat_message';
    }

    /**
     * override parent method to change charset of db connection for current model.
     * It needs for emoji support in chats. Charset was changed only for current model to prevent bugs with encoding
     * on the platform
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        if (empty(static::$modifiedDb)) {
            static::$modifiedDb = clone(parent::getDb());
            static::$modifiedDb->charset = "utf8mb4";
        }
        return static::$modifiedDb;
    }

    /**
     * @return ChatMessageQuery
     */
    public static function find()
    {
        return new ChatMessageQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message'], 'string'],
            [['isCompanyClient'], 'boolean'],
            [['date_sent', 'sender_id', 'recipient_id'], 'integer'],
            [['createdAt', 'updatedAt'], 'safe'],
            [['recipientStatusRead'], 'boolean'],
            [['_id', 'chat_dialog_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            '_id' => 'Id',
            'message' => 'Message',
            'chat_dialog_id' => 'Chat Dialog ID',
            'date_sent' => 'Date Sent',
            'sender_id' => 'Sender ID',
            'recipient_id' => 'Recipient ID',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
            ],
        ];
    }

    public static function typesArray()
    {
        return [
            'chat' => static::TYPE_CHAT,
            'image' => static::TYPE_IMAGE,
            'video' => static::TYPE_VIDEO,
            'audio' => static::TYPE_AUDIO,
            'other' => static::TYPE_OTHER,
        ];
    }

    public function setMessageType($type)
    {
        $typeId = static::TYPE_CHAT;
        $types = static::typesArray();
        if (isset($types[$type])) {
            $typeId = $types[$type];
        }
        $this->messageType = $typeId;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessageSender()
    {
        return $this->hasOne(AccountWithDeleted::className(), ['id' => 'accountId'])->viaTable(Chat::tableName() . ' senderChat', ['chatUserId' => 'sender_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessageRecipient()
    {
        return $this->hasOne(AccountWithDeleted::className(), ['id' => 'accountId'])->viaTable(Chat::tableName() . ' recipientChat', ['chatUserId' => 'recipient_id']);
    }

    /**
     * mark as read all recent unread messages
     */
    public function markAsReadRecentMessages()
    {
        $condition = [
            'and',
            ['<', 'date_sent', $this->date_sent],
            ['recipientStatusRead' => false],
            ['chat_dialog_id' => $this->chat_dialog_id],
            ['recipient_id' => $this->recipient_id]
        ];
        return Yii::$app->db->createCommand()->update(static::tableName(), ['recipientStatusRead' => true], $condition)->execute();
    }

    public static function markAllUserMessagesAsRead($chatDialogId, $userId, $dateTo = null)
    {
        $condition = [
            'chat_dialog_id' => $chatDialogId,
            'recipient_id' => $userId,
            'recipientStatusRead' => 0,
        ];
        if (!empty($dateSent)) {
            $condition[] = ['<', 'date_sent', $dateTo];
        }
        return \Yii::$app->db->createCommand()->update(
            ChatMessage::tableName(),
            ['recipientStatusRead' => 1],
            $condition
        )->execute();
    }
}

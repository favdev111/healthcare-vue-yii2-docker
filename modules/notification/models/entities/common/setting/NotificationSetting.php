<?php

namespace modules\notification\models\entities\common\setting;

use modules\account\models\Account;
use modules\notification\activeQuery\common\NotificationSettingQuery;

/**
 * This is the model class for table "notification_setting".
 *
 * @property int $accountId
 * @property int $notificationTypeId
 *
 * @property-read NotificationType $notificationType
 * @property Account $account
 */
class NotificationSetting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notification_setting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['accountId', 'notificationTypeId'], 'required'],
            [['accountId', 'notificationTypeId'], 'integer'],
            ['accountId', 'exist', 'skipOnError' => true, 'targetClass' => Account::class, 'targetAttribute' => 'id'],
            ['notificationTypeId', 'exist', 'skipOnError' => true, 'targetClass' => NotificationType::class, 'targetAttribute' => 'id'],
            [['accountId', 'notificationTypeId'], 'unique', 'targetAttribute' => ['accountId', 'notificationTypeId']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'accountId' => 'Account ID',
            'notificationTypeId' => 'Notification Type',
        ];
    }

    /**
     * Gets query for [[Account]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'accountId']);
    }

    /**
     * Gets query for [[Account]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNotificationType()
    {
        return $this->hasOne(NotificationType::class, ['notificationTypeId' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return NotificationSettingQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new NotificationSettingQuery(get_called_class());
    }
}

<?php

namespace modules\notification\activeQuery\api2;

use modules\account\models\Account;
use modules\notification\models\entities\common\Notification;

/**
 * This is the ActiveQuery class for [[Notification]].
 *
 * @see Notification
 */
class NotificationQuery extends \yii\db\ActiveQuery
{
    /**
     * @return NotificationQuery
     */
    public function notifiableAccount(): NotificationQuery
    {
        return $this->andWhere(['notifiable_type' => Account::class]);
    }

    /**
     * @return NotificationQuery
     */
    public function unread(): NotificationQuery
    {
        return $this->andWhere(['read_at' => null]);
    }

    /**
     * {@inheritdoc}
     * @return Notification[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Notification|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}

<?php

namespace modules\notification\models\entities\common;

use modules\notification\activeQuery\api2\NotificationQuery;

/**
 * Class Notification
 * @package modules\notification\models\entities
 * @property int $id [int]
 * @property int $created_at [timestamp]
 * @property int $updated_at [timestamp]
 */
class Notification extends \tuyakhov\notifications\models\Notification
{
    /**
     * @return NotificationQuery|\yii\db\ActiveQuery
     */
    public static function find()
    {
        return new NotificationQuery(get_called_class());
    }
}

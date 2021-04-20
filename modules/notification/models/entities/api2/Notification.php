<?php

namespace modules\notification\models\entities\api2;

use DateTime;

/**
 * Class Notification
 * @package modules\notification\models\entities\api2
 * @property int $id [int]
 * @property int $created_at [timestamp]
 * @property int $updated_at [timestamp]
 */
class Notification extends \modules\notification\models\entities\common\Notification
{
    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'createdAt' => function () {
                return (new DateTime($this->created_at))->format(DateTime::ATOM);
            },
            'message' => 'body',
            'type' => function () {
                return $this->data('notificationTypeId');
            },
            'isRead' => function () {
                return $this->read_at !== null;
            }
        ];
    }
}

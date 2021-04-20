<?php

namespace modules\notification\models\entities\api2\setting;

/**
 * Class NotificationCategory
 * @package modules\notification\models\entities\api2\setting
 *
 * @property-read int $id
 * @property-read string $name
 */
class NotificationCategory extends \modules\notification\models\entities\common\setting\NotificationCategory
{
    /**
     * @return string[]
     */
    public function fields()
    {
        return [
            'id',
            'name'
        ];
    }
}

<?php

namespace modules\notification\models\entities\common\setting;

use common\components\db\file\ActiveRecord;

/**
 * Class NotificationCategory
 * @package modules\notification\models\entities\common\setting
 *
 * @property-read int $id
 * @property-read string $name
 */
class NotificationCategory extends ActiveRecord
{
    public const CATEGORY_EMAIL = 1;
    public const CATEGORY_SMS = 2;

    /**
     * @return array|string
     */
    public static function fileName()
    {
        return 'modules/notification/data/setting/NotificationCategory';
    }
}

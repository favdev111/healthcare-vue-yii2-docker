<?php

namespace modules\notification\models\entities\common\setting;

use common\components\db\file\ActiveRecord;

/**
 * Class NotificationType
 * @package modules\notification\models\entities\common\setting
 *
 * @property-read int $id
 * @property-read string $name
 * @property-read int $categoryId
 *
 * @property-read \yii2tech\filedb\ActiveQuery $category
 */
class NotificationType extends ActiveRecord
{
    /**
     * @return array|string
     */
    public static function fileName()
    {
        return 'modules/notification/data/setting/NotificationType';
    }

    /**
     * @return \yii2tech\filedb\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(NotificationCategory::class, ['id' => 'categoryId']);
    }
}

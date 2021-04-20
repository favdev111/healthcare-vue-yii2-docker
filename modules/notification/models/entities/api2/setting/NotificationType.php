<?php

namespace modules\notification\models\entities\api2\setting;

use modules\notification\models\entities\common\setting\NotificationSetting;
use Yii;

/**
 * Class NotificationType
 * @package modules\notification\models\entities\api2\setting
 *
 * @property-read int $id
 * @property-read string $name
 * @property-read int $categoryId
 *
 * @property-read \yii2tech\filedb\ActiveQuery $category
 */
class NotificationType extends \modules\notification\models\entities\common\setting\NotificationType
{
    /**
     * @return string[]
     */
    public function fields()
    {
        return [
            'id',
            'name',
            'category',
            'isSet' => function () {
                return NotificationSetting::find()
                    ->where(['accountId' => Yii::$app->user->getId()])
                    ->andWhere(['notificationTypeId' => $this->id])
                    ->exists();
            }
        ];
    }
}

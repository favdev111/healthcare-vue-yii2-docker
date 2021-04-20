<?php

namespace modules\account\models\api2\health;

/**
 * This is the model class for table "lifestyle_diet".
 *
 * @property int $id
 * @property string $name
 * @property string|null $createdAt
 * @property string|null $updatedAt
 */
class LifestyleDiet extends \common\models\health\LifestyleDiet
{
    public function fields()
    {
        return [
            'id',
            'name',
        ];
    }
}

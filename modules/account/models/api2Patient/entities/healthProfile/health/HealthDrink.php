<?php

namespace modules\account\models\api2Patient\entities\healthProfile\health;

/**
 * Class HealthDrink
 * @package common\models\healthProfile\health
 *
 * @property-read int $id
 * @property-read string $name
 */
class HealthDrink extends \common\models\healthProfile\health\HealthDrink
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

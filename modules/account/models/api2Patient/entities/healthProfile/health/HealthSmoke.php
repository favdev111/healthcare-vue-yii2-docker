<?php

namespace modules\account\models\api2Patient\entities\healthProfile\health;

/**
 * Class HealthSmoke
 * @package common\models\healthProfile\health
 *
 * @property-read int $id
 * @property-read string $name
 */
class HealthSmoke extends \common\models\healthProfile\health\HealthSmoke
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

<?php

namespace common\models\healthProfile\health;

use common\components\db\file\ActiveRecord;

/**
 * Class HealthDrink
 * @package common\models\healthProfile\health
 *
 * @property-read int $id
 * @property-read string $name
 */
class HealthDrink extends ActiveRecord
{
    /**
     * @return string
     */
    public static function fileName()
    {
        return 'common/data/healthProfile/health/HealthDrink';
    }
}

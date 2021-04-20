<?php

namespace common\models\healthProfile\health;

use common\components\db\file\ActiveRecord;

/**
 * Class HealthSmoke
 * @package common\models\healthProfile\health
 *
 * @property-read int $id
 * @property-read string $name
 */
class HealthSmoke extends ActiveRecord
{
    /**
     * @return string
     */
    public static function fileName()
    {
        return 'common/data/healthProfile/health/HealthSmoke';
    }
}

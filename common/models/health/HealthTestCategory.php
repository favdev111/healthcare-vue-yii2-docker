<?php

namespace common\models\health;

use common\components\ActiveRecord;

/**
 * Class HealthTest
 * @property integer $id
 * @property string $name
 * @property string $createdAt
 * @property string $updatedAt
 * @package modules\account\models\ar
 */
class HealthTestCategory extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
            ],
        ];
    }
}

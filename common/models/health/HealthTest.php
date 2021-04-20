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
class HealthTest extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%health_test}}';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['createdAt', 'updatedAt'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

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

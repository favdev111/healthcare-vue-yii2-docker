<?php

namespace common\models\health;

use common\components\ActiveRecord;
use common\components\behaviors\SluggableBehavior;
use common\components\validators\SlugValidator;

/**
 * Class Symptom
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $slug
 * @property string $createdAt
 * @property string $updatedAt
 * @package modules\account\models\ar
 */
class Symptom extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%symptom}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['name', 'required'],
            [['description'], 'string'],
            [['createdAt', 'updatedAt'], 'safe'],
            [['name', 'slug'], 'string', 'max' => 255],
            ['slug', SlugValidator::class],
            ['slug', 'unique'],
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
            'slug' => [
                'class' => SluggableBehavior::class,
            ],
        ];
    }
}

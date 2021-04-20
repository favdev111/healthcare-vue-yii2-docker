<?php

namespace common\models\health;

use Yii;

/**
 * This is the model class for table "lifestyle_diet".
 *
 * @property int $id
 * @property string $name
 * @property string|null $createdAt
 * @property string|null $updatedAt
 */
class LifestyleDiet extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lifestyle_diet';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['createdAt', 'updatedAt'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * @return string[][]
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return \common\models\query\LifestyleDietQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\LifestyleDietQuery(get_called_class());
    }
}

<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%google_place}}".
 *
 * @property int $id
 * @property string $placeId
 * @property array $data
 * @property string $createdAt
 */
class GooglePlace extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%google_place}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['placeId'], 'required'],
            [['data'], 'safe'],
            [['placeId'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'placeId' => 'Place ID',
            'data' => 'Data',
            'createdAt' => 'Created At',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => \common\components\behaviors\TimestampBehavior::class,
                'updatedAtAttribute' => null,
            ],
        ];
    }
}

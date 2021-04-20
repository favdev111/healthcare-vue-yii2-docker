<?php

namespace common\models;

/**
 * This is the model class for table "{{%location_zipcode}}".
 *
 * @property integer $id
 * @property integer $code
 * @property float $latitude
 * @property float $longitude
 * @property integer $cityId
 * @property string $createdAt
 *
 * @property City $city
 */
class Zipcode extends \yii\db\ActiveRecord
{
    public static function cacheTagAttributes()
    {
        return ['id', 'code'];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%location_zipcode}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
                'updatedAtAttribute' => null,
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::class, ['id' => 'cityId']);
    }
}

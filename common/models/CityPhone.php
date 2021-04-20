<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "citiesPhones".
 *
 * @property integer $id
 * @property integer $cityId
 * @property string $phone
 *
 * @property City $city
 */
class CityPhone extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cities_phones}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cityId'], 'integer'],
            [['phone'], 'string', 'max' => 255],
            [['cityId'], 'exist', 'skipOnError' => true, 'targetClass' => City::class, 'targetAttribute' => ['cityId' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cityId' => 'City ID',
            'phone' => 'Phone',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::class, ['id' => 'cityId'])->inverseOf('phone');
    }
}

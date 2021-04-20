<?php

namespace common\models\healthProfile\health;

use common\models\healthProfile\HealthProfile;
use yii\behaviors\AttributeTypecastBehavior;

/**
 * This is the model class for table "health_profile_health".
 *
 * @property int $healthProfileId
 * @property int|null $smokeId
 * @property int|null $drinkId
 * @property int|null $isOtherSubstance
 * @property string|null $otherSubstanceText
 *
 * @property-read HealthDrink|null $drink
 * @property-read HealthSmoke|null $smoke
 * @property HealthProfile $healthProfile
 */
class HealthProfileHealth extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'health_profile_health';
    }

    /**
     * @return array|array[]
     */
    public function behaviors()
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'isOtherSubstance' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                ],
                'typecastAfterValidate' => true,
                'typecastBeforeSave' => true,
                'typecastAfterSave' => true,
                'typecastAfterFind' => true,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                'isOtherSubstance',
                'boolean',
                'trueValue' => true,
                'falseValue' => false,
                'strict' => true
            ],
            [['healthProfileId'], 'required'],
            [['healthProfileId', 'smokeId', 'drinkId'], 'integer'],
            [['otherSubstanceText'], 'string'],
            [['healthProfileId'], 'unique'],
            ['healthProfileId', 'exist', 'skipOnError' => true, 'targetClass' => HealthProfile::class, 'targetAttribute' => 'id'],
            ['smokeId', 'exist', 'skipOnError' => true, 'targetClass' => HealthSmoke::class, 'targetAttribute' => 'id'],
            ['drinkId', 'exist', 'skipOnError' => true, 'targetClass' => HealthDrink::class, 'targetAttribute' => 'id'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'healthProfileId' => 'Health Profile ID',
            'smokeId' => 'Smoke ID',
            'drinkId' => 'Drink ID',
            'isOtherSubstance' => 'Is Other Substance',
            'otherSubstanceText' => 'Other Substance Text',
        ];
    }

    /**
     * Gets query for [[HealthProfile]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\HealthProfileQuery
     */
    public function getHealthProfile()
    {
        return $this->hasOne(HealthProfile::className(), ['id' => 'healthProfileId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSmoke()
    {
        return $this->hasOne(HealthSmoke::class, ['id' => 'smokeId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDrink()
    {
        return $this->hasOne(HealthDrink::class, ['id' => 'drinkId']);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\query\HealthProfileHealthQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\HealthProfileHealthQuery(get_called_class());
    }
}

<?php

namespace common\models\healthProfile\health\medical;

use common\models\health\allergy\Allergy;
use common\models\health\allergy\AllergyCategory;
use common\models\health\AutoimmuneDisease;
use common\models\health\HealthGoal;
use common\models\health\LifestyleDiet;
use common\models\health\MedicalCondition;
use common\models\health\Symptom;
use common\models\healthProfile\HealthProfile;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;

/**
 * This is the model class for table "health_profile_health_medical".
 *
 * @property int|null $id
 * @property int $healthProfileId
 * @property string|null $text
 * @property int|null $internalId
 * @property int $medicalTypeId
 *
 * @property-read \yii\db\ActiveQuery|\common\models\healthProfile\health\medical\HealthMedicalType $type
 * @property-read \yii\db\ActiveQuery|\common\models\health\allergy\Allergy $foodIntolerance
 * @property-read \yii\db\ActiveQuery|\common\models\health\AutoimmuneDisease $autoimmuneDisease
 * @property-read \yii\db\ActiveQuery|\common\models\health\allergy\Allergy $allergy
 * @property-read \yii\db\ActiveQuery|\common\models\health\HealthGoal $healthGoal
 * @property-read \yii\db\ActiveQuery|\common\models\health\LifestyleDiet $lifestyleDiet
 * @property-read \yii\db\ActiveQuery|\common\models\health\MedicalCondition $medicalCondition
 * @property-read \common\models\health\Symptom|\yii\db\ActiveQuery $symptom
 * @property-read \yii\db\ActiveQuery|\common\models\health\allergy\AllergyCategory $allergyCategory
 * @property HealthProfile $healthProfile
 */
class HealthProfileHealthMedical extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'id' => AttributeTypecastBehavior::TYPE_INTEGER,
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
    public static function tableName()
    {
        return 'health_profile_health_medical';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'healthProfileId', 'internalId', 'medicalTypeId'], 'integer'],
            [['healthProfileId', 'medicalTypeId'], 'required'],
            [['text'], 'string'],
            [['healthProfileId'], 'exist', 'skipOnError' => true, 'targetClass' => HealthProfile::className(), 'targetAttribute' => ['healthProfileId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'healthProfileId' => 'Health Profile ID',
            'text' => 'Text',
            'internalId' => 'Internal ID',
            'medicalTypeId' => 'Medical Type ID',
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
     * @return \yii\db\ActiveQuery|Symptom
     */
    public function getSymptom()
    {
        return $this->hasOne(Symptom::class, ['id' => 'internalId']);
    }

    /**
     * @return \yii\db\ActiveQuery|MedicalCondition
     */
    public function getMedicalCondition()
    {
        return $this->hasOne(MedicalCondition::class, ['id' => 'internalId']);
    }

    /**
     * @return \yii\db\ActiveQuery|AutoimmuneDisease
     */
    public function getAutoimmuneDisease()
    {
        return $this->hasOne(AutoimmuneDisease::class, ['id' => 'internalId']);
    }

    /**
     * @return \yii\db\ActiveQuery|Allergy
     */
    public function getAllergy()
    {
        return $this->hasOne(Allergy::class, ['id' => 'internalId']);
    }

    /**
     * @return \yii\db\ActiveQuery|AllergyCategory
     */
    public function getAllergyCategory()
    {
        return $this->hasOne(AllergyCategory::class, ['id' => 'internalId']);
    }

    /**
     * @return \yii\db\ActiveQuery|Allergy
     */
    public function getFoodIntolerance()
    {
        return $this->hasOne(Allergy::class, ['id' => 'internalId']);
    }

    /**
     * @return \yii\db\ActiveQuery|LifestyleDiet
     */
    public function getLifestyleDiet()
    {
        return $this->hasOne(LifestyleDiet::class, ['id' => 'internalId']);
    }

    /**
     * @return \yii\db\ActiveQuery|HealthGoal
     */
    public function getHealthGoal()
    {
        return $this->hasOne(HealthGoal::class, ['id' => 'internalId']);
    }

    /**
     * @return \yii\db\ActiveQuery|HealthMedicalType
     */
    public function getType()
    {
        return $this->hasOne(HealthMedicalType::class, ['id' => 'medicalTypeId']);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\query\HealthProfileHealthMedicalQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\HealthProfileHealthMedicalQuery(get_called_class());
    }
}

<?php

namespace modules\account\models\api2Patient\entities\healthProfile\health\medical;

use common\models\healthProfile\HealthProfile;

/**
 * This is the model class for table "health_profile_health_medical".
 *
 * @property int|null $id
 * @property int $healthProfileId
 * @property string|null $text
 * @property int|null $internalId
 * @property int $medicalTypeId
 *
 * @property-read null|string|int $value
 * @property-read \common\models\health\Symptom|\yii\db\ActiveQuery|\common\models\health\HealthGoal|\common\models\health\allergy\Allergy|\common\models\health\MedicalCondition|\common\models\health\LifestyleDiet|\common\models\health\AutoimmuneDisease $typeEntity
 * @property-read array $internalIdFields
 * @property HealthProfile $healthProfile
 */
class HealthProfileHealthMedical extends \common\models\healthProfile\health\medical\HealthProfileHealthMedical
{
    /**
     * @return string[]
     */
    public function fields()
    {
        return match(true) {
            $this->type->isSymptom(),
            $this->type->isMedicalConditions(),
            $this->type->isAutoImmune(),
            $this->type->isAllergiesCategory(),
            $this->type->isFoodIntolerances(),
            $this->type->isFoodIntolerancesCategory(),
            $this->type->isLifestyleDiet(),
            $this->type->isHealthGoal() => $this->internalIdFields,
            $this->type->isAllergies() => array_merge([
                'allergyCategoryId' => function () {
                    return $this->allergy->allergyCategoryId;
                },
            ], $this->internalIdFields),
            $this->type->isCurrentMedications(),
            $this->type->isOther(),
            $this->type->isHealthConcern() => [
                'id',
                'text'
            ],
            };
    }

    protected function getInternalIdFields(): array
    {
        return [
            'id',
            'internalId',
            'name' => function () {
                return $this->typeEntity->name;
            }
        ];
    }

    /**
     * @return \common\models\health\allergy\Allergy|\common\models\health\AutoimmuneDisease|\common\models\health\HealthGoal|\common\models\health\LifestyleDiet|\common\models\health\MedicalCondition|\common\models\health\Symptom|\yii\db\ActiveQuery
     */
    public function getTypeEntity()
    {
        return match(true) {
            $this->type->isSymptom() => $this->symptom,
            $this->type->isMedicalConditions() => $this->medicalCondition,
            $this->type->isAutoImmune() => $this->autoimmuneDisease,
            $this->type->isAllergies() => $this->allergy,
            $this->type->isAllergiesCategory(),
            $this->type->isFoodIntolerancesCategory() => $this->allergyCategory,
            $this->type->isFoodIntolerances() => $this->foodIntolerance,
            $this->type->isLifestyleDiet() => $this->lifestyleDiet,
            $this->type->isHealthGoal() => $this->healthGoal,
        };
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
}

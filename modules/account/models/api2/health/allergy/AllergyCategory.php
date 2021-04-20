<?php

namespace modules\account\models\api2\health\allergy;

use common\models\healthProfile\health\MedicalAllergyGroup;

/**
 * This is the model class for table "allergy_category".
 *
 * @property int $id
 * @property string $name
 *
 * @property Allergy[] $allergies
 * @property MedicalAllergyGroup $medicalAllergyGroup
 */
class AllergyCategory extends \common\models\health\allergy\AllergyCategory
{
    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'name',
            'isMedicalGroup' => function () {
                return $this->medicalAllergyGroup !== null;
            },
        ];
    }

    /**
     * Gets query for [[Allergies]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\AllergyQuery
     */
    public function getAllergies()
    {
        return $this->hasMany(Allergy::className(), ['allergyCategoryId' => 'id']);
    }

    /**
     * Gets query for [[MedicalAllergyGroup]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\MedicalAllergyGroupQuery
     */
    public function getMedicalAllergyGroup()
    {
        return $this->hasOne(MedicalAllergyGroup::className(), ['allergyCategoryId' => 'id']);
    }
}

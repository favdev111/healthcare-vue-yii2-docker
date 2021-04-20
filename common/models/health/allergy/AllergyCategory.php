<?php

namespace common\models\health\allergy;

use common\models\healthProfile\health\MedicalAllergyGroup;
use Yii;

/**
 * This is the model class for table "allergy_category".
 *
 * @property int $id
 * @property string $name
 *
 * @property Allergy[] $allergies
 * @property MedicalAllergyGroup $medicalAllergyGroup
 */
class AllergyCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'allergy_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
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

    /**
     * {@inheritdoc}
     * @return \common\models\query\AllergyCategoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\AllergyCategoryQuery(get_called_class());
    }
}

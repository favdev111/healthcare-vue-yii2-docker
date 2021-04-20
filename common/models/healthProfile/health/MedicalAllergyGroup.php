<?php

namespace common\models\healthProfile\health;

use common\models\health\allergy\AllergyCategory;
use Yii;

/**
 * This is the model class for table "medical_allergy_group".
 *
 * @property int $allergyCategoryId
 *
 * @property AllergyCategory $allergyCategory
 * @property int $allergy_category_id [int unsigned]
 */
class MedicalAllergyGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'medical_allergy_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['allergyCategoryId'], 'exist', 'skipOnError' => true, 'targetClass' => AllergyCategory::className(), 'targetAttribute' => ['allergyCategoryId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'allergyCategoryId' => 'Allergy Category ID',
        ];
    }

    /**
     * Gets query for [[AllergyCategory]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\AllergyCategoryQuery
     */
    public function getAllergyCategory()
    {
        return $this->hasOne(AllergyCategory::className(), ['id' => 'allergyCategoryId']);
    }

    /**
     * {@inheritdoc}
     * @return \common\models\query\MedicalAllergyGroupQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\MedicalAllergyGroupQuery(get_called_class());
    }
}

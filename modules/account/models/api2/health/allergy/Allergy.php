<?php

namespace modules\account\models\api2\health\allergy;

/**
 * This is the model class for table "allergy".
 *
 * @property int $id
 * @property int $allergyCategoryId
 * @property string $name
 * @property string|null $createdAt
 * @property string|null $updatedAt
 *
 * @property AllergyCategory $allergyCategory
 */
class Allergy extends \common\models\health\allergy\Allergy
{
    public function fields()
    {
        return [
            'id',
            'name',
            'allergyCategoryId'
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
}

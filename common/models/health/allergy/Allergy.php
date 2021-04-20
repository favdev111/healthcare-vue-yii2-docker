<?php

namespace common\models\health\allergy;

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
class Allergy extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'allergy';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['allergyCategoryId', 'name'], 'required'],
            [['allergyCategoryId'], 'integer'],
            [['createdAt', 'updatedAt'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['allergyCategoryId', 'name'], 'unique', 'targetAttribute' => ['allergyCategoryId', 'name']],
            [['allergyCategoryId'], 'exist', 'skipOnError' => true, 'targetClass' => AllergyCategory::className(), 'targetAttribute' => ['allergyCategoryId' => 'id']],
        ];
    }

    /**
     * @return string[][]
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'allergyCategoryId' => 'Allergy Category ID',
            'name' => 'Name',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
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
     * @return \common\models\query\AllergyQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\AllergyQuery(get_called_class());
    }
}

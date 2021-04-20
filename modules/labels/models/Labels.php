<?php

namespace modules\labels\models;

use common\components\behaviors\TimestampBehavior;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%labels}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $status
 * @property string $color
 * @property integer $categoryId
 * @property string $createdAt
 * @property string $updatedAt
 */
class Labels extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%labels}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'categoryId'], 'integer'],
            [['categoryId', 'name', 'status'], 'required'],
            [['createdAt', 'updatedAt'], 'safe'],
            [['name', 'color'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'status' => 'Status',
            'color' => 'Color',
            'categoryId' => 'Category ID',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(LabelsCategory::class, ['id' => 'categoryId']);
    }

    /**
     * @param string $slug
     * @return ActiveQuery
     */
    public function getCategoryBySlug(string $slug): ActiveQuery
    {
        return $this->hasOne(LabelsCategory::class, ['id' => 'categoryId'])->andWhere(['slug' => $slug]);
    }
}

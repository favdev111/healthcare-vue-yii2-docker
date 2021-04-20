<?php

namespace modules\labels\models;

use common\components\behaviors\TimestampBehavior;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%labels_category}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $slug
 * @property string $createdAt
 * @property string $updatedAt
 */
class LabelsCategory extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%labels_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['createdAt', 'updatedAt', 'slug'], 'safe'],
            [['name'], 'string', 'max' => 255],
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
            'slug' => 'Slug',
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
            'subjectSlug' => [
                'class' => SluggableBehavior::class,
                'attribute' => 'name',
                'slugAttribute' => 'slug',
            ],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getLabels(): ActiveQuery
    {
        return $this->hasMany(Labels::class, ['categoryId' => 'id']);
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        if (!$this->isNewRecord) {
            $this->detachBehavior('subjectSlug');
        }
        return parent::beforeValidate();
    }

    public function fields()
    {
        return ['id', 'name', 'slug'];
    }
}

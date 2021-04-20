<?php

namespace modules\account\models;

use common\components\behaviors\CreatedUpdatedBehavior;
use common\components\behaviors\TimestampBehavior;
use Yii;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "grade_items".
 *
 * @property int $itemId
 * @property int $itemType
 * @property string $gradeId
 * @property string $createdAt
 * @property string $updatedAt
 * @property string $deletedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property Grade $grade
 */
class GradeItem extends \yii\db\ActiveRecord
{
    const TYPE_ACCOUNT_PROFILE = 1;
    const TYPE_CLIENT_CHILD = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%grade_items}}';
    }

    public function behaviors()
    {
        return [
            'createdUpdated' => [
                'class' => CreatedUpdatedBehavior::class,
            ],
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'value' => date('Y-m-d H:i:s'),
            ],
            'softDeleteBehavior' => [
                'class' => SoftDeleteBehavior::class,
                'softDeleteAttributeValues' => [
                    'deletedAt' => date('Y-m-d H:i:s'),
                ],
                'replaceRegularDelete' => true
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['itemId', 'itemType', 'gradeId'], 'required'],
            [['itemId', 'itemType', 'gradeId'], 'integer'],
            [
                ['gradeId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Grade::class,
                'targetAttribute' => ['gradeId' => 'id']
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'itemId' => 'Item ID',
            'itemType' => 'Item Type',
            'gradeId' => 'Grade ID',
        ];
    }

    public function isPossibleUpdate()
    {
        return !empty($this->grade->updateGroup) && $this->grade->next()->exists();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGrade()
    {
        return $this->hasOne(Grade::class, ['id' => 'gradeId']);
    }

    /**
     * {@inheritdoc}
     * @return \modules\account\models\query\GradeItemsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \modules\account\models\query\GradeItemsQuery(get_called_class());
    }

    public function toNextGrade()
    {
        if ($this->isPossibleUpdate()) {
            $nextGrade = $this->grade->next()->one();
            $this->gradeId = $nextGrade->id;
        }
    }
}

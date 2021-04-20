<?php

namespace modules\account\models;

/**
 * This is the model class for table "{{%category_subject}}".
 *
 * @property integer $categoryId
 * @property integer $subjectId
 */
class CategorySubject extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%category_subject}}';
    }
}

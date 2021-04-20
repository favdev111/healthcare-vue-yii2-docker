<?php

namespace modules\account\models;

use Yii;
use modules\account\models\AccountEducation;
use common\components\HtmlPurifier;

/**
 * This is the model class for table "{{%education_degree}}".
 *
 * @property integer $id
 * @property string $name
 *
 * @property AccountEducation[] $accountEducations
 */
class EducationDegree extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%education_degree}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], function ($attribute) {
                $this->$attribute = HtmlPurifier::process($this->$attribute, ['HTML.Allowed' => '']);
            }
            ],
            [['name'], 'required'],
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountEducations()
    {
        return $this->hasMany(AccountEducation::className(), ['degreeId' => 'id']);
    }
}

<?php

namespace modules\account\models;

use Yii;

/**
 * This is the model class for table "automatch_subjects".
 *
 * @property int $id
 * @property int $subjectId
 *
 * @property Subject $subject
 */
class AutomatchSubject extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%automatch_subjects}}';
    }

    public static function allIds()
    {
        return static::find()->select('subjectId')->column();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['subjectId'], 'integer'],
            [
                ['subjectId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Subject::class,
                'targetAttribute' => ['subjectId' => 'id']
            ],
            [['subjectId'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'subjectId' => 'Subject ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubject()
    {
        return $this->hasOne(Subject::class, ['id' => 'subjectId']);
    }
}

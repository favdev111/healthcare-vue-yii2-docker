<?php

namespace modules\account\models;

use Yii;

/**
 * This is the model class for table "{{%job_subject}}".
 *
 * @property integer $id
 * @property integer $subjectId
 * @property integer $jobId
 *
 * @property Job $job
 * @property Subject $subject
 */
class JobSubject extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%job_subject}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subjectId', 'jobId'], 'required'],
            [['subjectId', 'jobId'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'subjectId' => 'Subject ID',
            'jobId' => 'Job ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJob()
    {
        return $this->hasOne(Job::className(), ['id' => 'jobId'])->andOnCondition(['!=', 'block', 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubject()
    {
        return $this->hasOne(Subject::className(), ['id' => 'subjectId']);
    }
}

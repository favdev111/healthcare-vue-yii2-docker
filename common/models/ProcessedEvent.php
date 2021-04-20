<?php

namespace common\models;

use common\models\query\ProcessedEventQuery;
use modules\account\models\Account;
use modules\account\models\Job;
use Yii;

/**
 * This is the model class for table "processed_events".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $accountId
 * @property integer $jobId
 * @property string $createdAt
 * @property string $data
 * @property integer $totalScore
 *
 * @property Account $account
 * @property Job $job
 *
 */
class ProcessedEvent extends \yii\db\ActiveRecord
{
    const TYPE_TUTOR_NOT_APPLIED = 1;
    const TYPE_TUTOR_NOTIFIED_ABOUT_NEW_JOB = 2;
    const TYPE_NEW_JOB_POSTED_NOTIFICATIONS_PROCESSED = 3;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%processed_events}}';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'required'],
            [['type', 'accountId', 'jobId'], 'integer'],
            [['accountId'], 'exist', 'skipOnError' => true, 'targetClass' => Account::class, 'targetAttribute' => ['accountId' => 'id']],
            [['jobId'], 'exist', 'skipOnError' => true, 'targetClass' => Job::class, 'targetAttribute' => ['jobId' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'accountId' => 'Account ID',
            'jobId' => 'Job ID',
            'createdAt' => 'Created At',
        ];
    }

    public static function find(): ProcessedEventQuery
    {
        return new ProcessedEventQuery(static::class);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'accountId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJob()
    {
        return $this->hasOne(Job::class, ['id' => 'jobId']);
    }
}

<?php

namespace modules\account\models;

use common\components\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class IgnoredTutorsJobNotification
 * @package modules\account\models
 * @property integer $originJobId
 * @property integer $jobId
 * @property integer $tutorId
 * @property string $createdAt
 * @property string $updatedAt
 */
class IgnoredTutorsJob extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%ignored_tutors_job}}';
    }

    /**
     * @return array
     */
    public function behaviors(): array
    {
        return ['timestamp' => ['class' => TimestampBehavior::class]];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['originJobId', 'jobId', 'tutorId'], 'required'],
            [['originJobId', 'jobId', 'tutorId'], 'integer'],
            [
                ['tutorId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Account::class,
                'targetAttribute' => ['tutorId' => 'id']
            ],
            [
                ['originJobId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Job::class,
                'targetAttribute' => ['originJobId' => 'id']
            ],
            [
                ['jobId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Job::class,
                'targetAttribute' => ['jobId' => 'id']
            ]
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'originJobId' => 'Origin job id',
            'jobId' => 'Reposted job ID',
            'tutorId' => 'Tutor ID',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }
}

<?php

namespace modules\yiiQueueHistory\models;

use common\components\ActiveRecord;

/**
 * Class History
 *
 * @property string $class
 * @property string $job
 * @property integer $accountId
 * @property integer $jobId
 * @property string $createdAt
 * @property string $status
 * @property string $error
 */
class History extends ActiveRecord
{
    const STATUS_PENDING = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%yii_queue_history}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['class', 'job', 'jobId'], 'required'],
            [['!accountId'], 'default', 'value' => \Yii::$app->user->id],
            [['status'], 'default', 'value' => static::STATUS_PENDING],
            [['error'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
                'updatedAtAttribute' => false,
            ],
        ];
    }
}

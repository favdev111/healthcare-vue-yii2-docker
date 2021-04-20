<?php

namespace modules\account\models;

use modules\chat\Module;
use Yii;

/**
 * This is the model class for table "rematch_job_hires".
 *
 * @property integer $id
 * @property integer $jobHireId
 * @property integer $accountReturnId
 *
 * @property bool $notifyTutor
 * @property AccountReturn $accountReturn
 * @property JobHire $jobHire
 */
class RematchJobHire extends \yii\db\ActiveRecord
{
    public $notifyTutor = false;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rematch_job_hires}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['jobHireId', 'accountReturnId'], 'required'],
            [['accountReturnId'], 'integer'],
            [['accountReturnId'], 'exist', 'skipOnError' => true, 'targetClass' => AccountReturn::class, 'targetAttribute' => ['accountReturnId' => 'id']],
            [['jobHireId'], 'exist', 'skipOnError' => true, 'targetClass' => JobHire::class, 'targetAttribute' => ['jobHireId' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'jobHireId' => 'Job Hire ID',
            'accountReturnId' => 'Account Return ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountReturn()
    {
        return $this->hasOne(AccountReturn::class, ['id' => 'accountReturnId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobHire()
    {
        return $this->hasOne(JobHire::class, ['id' => 'jobHireId']);
    }

    public function extraFields()
    {
        return array_merge(parent::extraFields(), [
            'jobHire',
            'accountReturn',
        ]);
    }


    public function notifyTutor()
    {
        $message = "Hi {$this->jobHire->tutor->profile->firstName}, we are updating you on your student match: we will be rematching {$this->jobHire->job->account->profile->fullName()} to accommodate the client's updated matching preferences. Please log in your last lesson with this client so that you can be paid as soon as possible before the client is removed from your student list. Please contact us if you have any questions or concerns";
        /**
         * @var Module $module
         */
        $module = \Yii::$app->moduleChat;
        $module->sendMessageQueue($message, $this->jobHire->job->account->chat, $this->jobHire->tutor->chat);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert && $this->notifyTutor && !empty($this->jobHire->tutor)) {
            $this->notifyTutor();
        }
    }
}

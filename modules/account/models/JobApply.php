<?php

namespace modules\account\models;

use common\helpers\Automatch;
use Yii;
use common\components\HtmlPurifier;

/**
 * This is the model class for table "{{%job_apply}}".
 *
 * @property integer $id
 * @property integer $jobId
 * @property integer $accountId
 * @property string $description
 * @property string $createdAt
 * @property string $updatedAt
 * @property boolean $isManual
 *
 * //related to automatching functionality
 * @property boolean $isOnlineBefore
 * @property integer $automatchScore
 * @property array $automatchData
 *
 * @property Account $account
 * @property Job $job
 */
class JobApply extends \yii\db\ActiveRecord
{
    const IS_ONLINE_BEFORE_ANSWER_BONUS_POINTS = 5;
    const PER_SUBJECT_MATCH_BONUS_POINTS = 6;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%job_apply}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], function ($attribute) {
                $this->$attribute = HtmlPurifier::process($this->$attribute, ['HTML.Allowed' => '']);
            }
            ],
            [['jobId'], 'required'],
            [['jobId'], 'integer'],
            [['jobId'], 'exist', 'skipOnError' => true, 'targetClass' => Job::class, 'targetAttribute' => ['jobId' => 'id']],
            [['isManual', 'isOnlineBefore'], 'boolean'],
            [['accountId'], 'applyExistsValidator'],
        ];
    }

    public function applyExistsValidator()
    {
        $isExists = static::find()
            ->andWhere(['jobId' => $this->jobId])
            ->andWhere(['accountId' => $this->accountId])
            ->exists();
        if ($isExists) {
            $this->addError('accountId', 'This tutor has already been applied to this job.');
        }
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'jobId' => 'Job ID',
            'accountId' => 'Account ID',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(AccountWithDeleted::class, ['id' => 'accountId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJob()
    {
        return $this->hasOne(JobWithSuspended::class, ['id' => 'jobId'])->andOnCondition(['!=', 'block', 1]);
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->automatchData = Automatch::calculatePoints($this);
            $this->automatchScore = $this->automatchData['total'] ?? 0;
        }
        return parent::beforeSave($insert);
    }

    /**
     * @return int
     */
    public function calculateAutomatchBonusPoints(): int
    {
        //bonus for answer `Have you tutored online before?` question
        $bonusPoints = $this->isOnlineBefore
            ? Automatch::getBonusPointValue(Automatch::APPLY_BONUS_KEY_TUTORED_ONLINE) : 0;

        //bonus for jobs's subjects in description
        $formattedDescription = strtolower($this->description);
        /**
         * @var string $subject
         */
        foreach ($this->job->getSubjectOrCategoryNamesArray() as $subject) {
            $subjectName = str_replace([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, '-'], '', $subject);
            $subjectName = trim($subjectName);
            $subjectName = strtolower($subjectName);

            if (strpos($formattedDescription, $subjectName) !== false) {
                $subjectPointValue = Automatch::getBonusPointValue(Automatch::APPLY_BONUS_KEY_SUBJECT) ?: 0;
                $bonusPoints += $subjectPointValue;
                $formattedDescription = str_replace($subjectName, '', $formattedDescription);
            }
        }

        return $bonusPoints;
    }
}

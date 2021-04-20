<?php

namespace modules\account\models;

use common\components\behaviors\TimestampBehavior;
use common\components\Formatter;
use modules\account\models\query\AccountQuery;
use modules\payment\Module;
use Yii;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "{{%job_hire}}".
 *
 * @property integer $id
 * @property integer $jobId
 * @property integer $tutorId
 * @property integer $status
 * @property integer $price
 * @property string $createdAt
 * @property string $updatedAt
 * @property integer $responsibleId
 * @property integer $tutoringHours
 * @property boolean $isManual
 *
 * @property Account $tutor
 * @property Account $student
 * @property Job $job
 * @property integer $formattedPrice
 * @property JobOffer $acceptedOffer
 * @property Account $responsible
 * @property float $billRate
 * @property float $margin
 * @property Lesson[] $lessons
 * @property-read  string $displayedTutoringHours
 * @property-read ChangeLog[] $changelist
 */
class JobHire extends \yii\db\ActiveRecord
{
    use ChangeLogTrait;

    const STATUS_HIRED = 1;
    const STATUS_DECLINED_BY_COMPANY = 0;
    const STATUS_DECLINED_BY_TUTOR = 2;
    const STATUS_CLOSED_BY_COMPANY = 3;

    public static $declineStatuses = [self::STATUS_DECLINED_BY_TUTOR, self::STATUS_DECLINED_BY_COMPANY];

    public $shareContactInfo = false;

    public static function find()
    {
        return new \modules\account\models\query\JobHireQuery(get_called_class());
    }

    public static function statusTexts()
    {
        return [
            self::STATUS_HIRED => 'Hired',
            self::STATUS_DECLINED_BY_COMPANY => 'Declined by company',
            self::STATUS_DECLINED_BY_TUTOR => 'Declined by tutor',
        ];
    }

    public static function statuses()
    {
        return array_keys(static::statusTexts());
    }

    public function getStatusText()
    {
        $statusArray = static::statusTexts();
        return $statusArray[$this->status] ?? null;
    }

    public function getResponsible()
    {
        return $this->hasOne(AccountWithDeleted::class, ['id' => 'responsibleId'])->alias('responsible');
    }

    public function isStatusClosedByCompany(): bool
    {
        return $this->status === static::STATUS_CLOSED_BY_COMPANY;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%job_hire}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['price'], 'double', 'min' => 1, 'tooSmall' => 'Please change the pay rate, it shouldn\'t be less than 1.'],
            [['shareContactInfo', 'isManual'], 'boolean'],
            [['jobId', 'tutorId'], 'integer'],
            [['jobId', 'tutorId', 'status'], 'required'],
            [['tutorId'], 'exist', 'skipOnError' => true, 'targetClass' => Account::className(), 'targetAttribute' => ['tutorId' => 'id'], 'filter' => function ($query) {
                /**
                 * @var $query AccountQuery
                 */
                $query->tutor();
            }
            ],
            'jobExists' => [['jobId'], 'exist', 'skipOnError' => true, 'targetClass' => Job::className(), 'targetAttribute' => ['jobId' => 'id']],
            'statuses' => ['status', 'in', 'range' => static::statuses()],
            ['tutorId', 'unique', 'targetAttribute' => ['jobId', 'tutorId'], 'message' => 'You can hire tutor for one job only once.'],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        foreach ($scenarios as $scenario) {
            $scenario['price'] = '!price';
        }
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $model = $this;
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'responsibleId',
                'updatedByAttribute' => false,
                'value' => function () use ($model) {
                    if (!empty($this->responsibleId)) {
                        return $this->responsibleId;
                    }
                    /**
                     * @var JobHire $model
                     */
                    //when tutor creates hire (accept company offer)
                    if (!empty(Yii::$app->user->identity) && Yii::$app->user->identity->isTutor()) {
                        $lastOffer = $model->job->latestJobOffer;
                        if (!empty($lastOffer)) {
                            return $lastOffer->responsibleId;
                        }
                    }

                    //in other cases return current user id
                    return Yii::$app->user->id ?? null;
                }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'jobId' => Yii::t('app', 'Job ID'),
            'tutorId' => Yii::t('app', 'Tutor ID'),
            'status' => Yii::t('app', 'Status'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTutor()
    {
        return $this->hasOne(Account::class, ['id' => 'tutorId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTutorWithoutRestrictions()
    {
        return $this->hasOne(AccountWithDeleted::class, ['id' => 'tutorId'])->alias('tutor');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(Account::class, ['id' => 'accountId'])->via('job');
    }

    public function getChangeList()
    {
        return $this->hasMany(ChangeLog::class, ['objectId' => 'id'])
            ->onCondition(['objectType' => ChangeLog::OBJECT_TYPE_JOB_HIRE]);
    }

    public function getStudentWithoutRestrictions()
    {
        return $this->hasOne(AccountWithDeleted::class, ['id' => 'accountId'])->via('job')->alias('student');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJob()
    {
        return $this->hasOne(Job::class, ['id' => 'jobId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAcceptedOffer()
    {
        return $this->hasOne(JobOffer::class, ['jobId' => 'jobId', 'tutorId' => 'tutorId'])
            ->andOnCondition([JobOffer::tableName() . '.status' => JobOffer::STATUS_CONFIRMED]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLessons()
    {
        return $this->hasMany(Lesson::class, ['jobId' => 'jobId', 'tutorId' => 'tutorId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobSubjects()
    {
        return $this->hasMany(Subject::class, ['id' => 'subjectId'])->viaTable(JobSubject::tableName(), ['jobId' => 'jobId']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        /**
         * @var $moduleAccount \modules\account\Module
         */
        $moduleAccount = \Yii::$app->getModule('account');

        if ($insert || isset($changedAttributes['status'])) {
            if ($this->status == self::STATUS_DECLINED_BY_COMPANY) {
                $moduleAccount->eventHireDeclinedByCompany($this);
            }
        }

        if ($this->status == self::STATUS_CLOSED_BY_COMPANY) {
            $isHiresExists = static::find()
                ->joinWith('job')
                ->andWhere([Job::tableName() . '.accountId' => $this->job->accountId])
                ->andWhere([JobHire::tableName() . '.status' => static::STATUS_HIRED])
                ->andWhere(['tutorId' => $this->tutorId])
                ->exists();
            if (!$isHiresExists) {
                $this->removeClientFromTutorsList();
            }
        }

        if (
            !$insert
            && isset($changedAttributes['price'])
            && ((float)$this->price !== (float)$changedAttributes['price'])
        ) {
            $this->changeLog(
                'price',
                JobHireRateChangeLog::class,
                $this->id,
                $changedAttributes
            );
        }

        if ($insert && $this->status == self::STATUS_HIRED) {
            /**
             * @var $moduleAccount \modules\account\Module
             */
            $moduleAccount = \Yii::$app->getModule('account');
            $moduleAccount->eventTutorHired($this);

            //disable automatch flag
            $this->job->isAutomatchEnabled = false;
            $this->job->save(false);
        }
    }

    public function getFormattedPrice()
    {
        if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isTutor()) {
            $result = $this->getTutorRate();
        } else {
            $result = $this->getFullAmount();
        }
        return Yii::$app->formatter->asInteger($result);
    }

    public function getTutorRate()
    {
        return round($this->price, 0);
    }

    public function getFullAmount()
    {
        /**
         * @var Module $paymentModule
         */
        $paymentModule = Yii::$app->getModule('payment');
        return round($paymentModule->getAmountWithCompanyCommissionForOfferOrHire($this->price, 0));
    }

    public function getSubjectsOrCategories()
    {
        return $this->job->getSubjectsOrCategories();
    }

    /**
     * @return float
     */
    public function getBillRate(): float
    {
        return $this->job->billRate ?? $this->student->rate->hourlyRate;
    }

    /**
     * @return float
     */
    public function getMargin(): float
    {
        $cost = $this->getFullAmount();
        $revenue = $this->billRate;
        return (($revenue - $cost) / $revenue) * 100;
    }

    public function updateTutoringHours()
    {
        $sum = 0;
        foreach ($this->lessons as $lesson) {
            $from = strtotime($lesson->fromDate);
            $to = strtotime($lesson->toDate);
            $sum += $to - $from;
        }
        $this->tutoringHours = $sum;
    }

    public function removeClientFromTutorsList()
    {
        $clientId = $this->job->accountId;
        if ($clientId) {
            ListAddedAccount::deleteAll(
                [
                    'ownerId' => $this->tutorId,
                    'accountId' => $clientId,
                ]
            );
        }
    }


    /**
     * @return false|string
     */
    public function getDisplayedTutoringHours(): string
    {
        /**
         * @var Formatter $formatter
         */
        $formatter = Yii::$app->formatter;
        return $formatter->getTimestampAsHoursAndMinutes($this->tutoringHours);
    }
}

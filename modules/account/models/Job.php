<?php

namespace modules\account\models;

use common\components\BusinessDaysCalculator;
use common\helpers\Automatch;
use common\helpers\AvailabilityHelper;
use common\helpers\Location;
use common\helpers\QueueHelper;
use common\helpers\Url;
use common\models\ProcessedEvent;
use common\models\query\ProcessedEventQuery;
use common\models\Zipcode;
use console\components\queueJobs\JobPostingOlderThan3DaysJob;
use modules\account\helpers\ConstantsHelper;
use modules\account\helpers\JobHelper;
use modules\account\models\FileModel;
use modules\account\models\Job as MainJob;
use modules\account\models\SubjectOrCategory\JobSubjectOrCategory;
use modules\account\models\SubjectOrCategory\SubjectOrCategory;
use modules\account\Module;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use common\components\ActiveRecord;
use common\components\HtmlPurifier;
use yii\queue\Queue;
use yii\validators\CompareValidator;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "{{%job}}".
 *
 * Find methods are overrode in order to hide suspended jobs by default
 *
 * @property integer $id
 * @property integer $accountId
 * @property integer $studentGrade
 * @property integer $lessonOccur
 * @property integer $gender
 * @property integer $ageFrom
 * @property integer $ageTo
 * @property integer $hourlyRateFrom
 * @property integer $hourlyRateTo
 * @property integer $startLesson
 * @property boolean $block
 * @property resource $availability
 * @property string $description
 * @property boolean $suspended
 * @property string $createdAt
 * @property string $updatedAt
 * @property string $nameWithLocationAndSubject
 * @property integer $close
 * @property integer $closeDate
 * @property boolean $newJob
 * @property boolean $forceSendingNotification
 * @property integer $zipCode
 * @property integer $notificationCycle
 * @property integer $allTutorsNotified
 * @property integer $autogenerate
 * @property integer $status
 * @property integer $viewed
 * @property integer $billRate
 * @property integer $countNotification
 * @property string $displayedDescription
 * @property string $xmlDescription
 * @property boolean $isRematchClose
 * @property int $originJobId
 * @property int $repostedJobId
 * @property int $isAutomatchEnabled
 * @property int $automatchJobId
 *
 * @property Account $account
 * @property ProcessedEvent[] $newJobPostedNotificationEvents
 * @property-read int $countProcessedBatches
 * @property JobSubject[] $jobSubjects
 * @property Account[] $applicants
 * @property JobOffer[] $jobOffers
 * @property JobHire[] $jobHires
 * @property JobHire[] $hiredJobHires
 * @property JobOffer $latestJobOffer
 * @property JobOffer $latestCompanyJobOffer
 * @property JobOffer $latestTutorJobOffer
 * @property JobHire $jobHire
 * @property integer $clientBillRate
 * @property-read  boolean $isOnline
 * @property-read string $cityName
 * @property bool $isNeedDisplayGender
 * @property array $notDeclinedApplies
 * @property-read int $automatchTimerEnd
 * @property-read bool $isAutomatchJob
 * @property-read AutomatchHistory $automatchHistory
 * @property-read ChangeLog[] $changelist
 */
class Job extends ActiveRecord
{
    use ChangeLogTrait;

    const PUBLISH = 1;
    const UNPUBLISH = 0;
    const NEW_JOB = 1;
    const STATUS_CLOSED = 1;
    const MOBILE_COUNT_LETTER_DESCRIPTION = 70;
    const LESSON_OCCUR_AT_HOME = 1;
    const LESSON_OCCUR_PUBLIC_LOCATION = 2;
    const LESSON_OCCUR_TUTORS_LOCATION = 3;
    const LESSON_OCCUR_ONLINE = 4;
    private $allRelatedSubjects; /*array of related subjects with subjects from selected categories*/
    public $statusTextArray = ['Unpublish', 'Publish'];
    public $subjects = [];
    public $files = [];
    public $availabilityArray = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%job}}';
    }

    /**
     * Is job closed
     *
     * @return bool
     */
    public function getIsClosed()
    {
        return (self::STATUS_CLOSED === $this->close);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenario = parent::scenarios();
        $scenario['autogenerate'] = ['studentGrade', 'lessonOccur', 'gender', 'hourlyRateFrom', 'hourlyRateTo', 'startLesson', 'description', 'subjects', 'zipCode'];
        return $scenario;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules =  [
            ['description', 'string', 'max' => 5000],
            [['gender'], 'default', 'value' => 'B'],
            [
                ['gender'],
                function ($attribute) {
                    $this->$attribute = HtmlPurifier::process($this->$attribute, ['HTML.Allowed' => '']);
                }
            ],
            'descriptionRemoveTags' => [
                ['description'],
                function ($attribute) {
                    $this->$attribute = HtmlPurifier::process($this->$attribute, ['HTML.Allowed' => '']);
                }
            ],
            [['studentGrade', 'lessonOccur', 'gender', 'hourlyRateTo', 'description', 'zipCode'], 'required'],

            [
                ['subjects'],
                'required',
                'when' => function () {
                    return !$this->getJobSubjects()->count();
                }
            ],
            ['availability', 'default', 'value' => 0],
            [['hourlyRateFrom'], 'default', 'value' => 20],
            [['ageFrom'], 'default', 'value' => 18],
            [['ageTo'], 'default', 'value' => 80],
            [['hourlyRateFrom', 'hourlyRateTo'], 'integer', 'min' => 20, 'max' => 250],
            [
                'hourlyRateTo',
                'compare',
                'compareAttribute' => 'hourlyRateFrom',
                'type' => CompareValidator::TYPE_NUMBER,
                'operator' => '>=',
                'skipOnError' => true
            ],
            [['billRate'], 'double', 'min' => 20, 'max' => 250],
            ['studentGrade', 'in', 'range' => array_keys($this->grade)],
            ['gender', 'in', 'range' => ['B', 'F', 'M']],
            ['lessonOccur', 'in', 'range' => array_keys(self::getLessonOccur())],
            ['startLesson', 'in', 'range' => array_keys(self::getStartLessonTime())],
            [['studentGrade', 'lessonOccur', 'startLesson'], 'integer'],
            [
                ['description'],
                'filter',
                'filter' => function ($value) {
                    $value = preg_replace(
                        '/[-0-9a-zA-Z.+_ ]+@[-0-9a-zA-Z.+_ ]+.[a-zA-Z ]{2,4}/',
                        '',
                        $value
                    );
                    return $value;
                }
            ],
            [['description', 'gender'], 'string'],
            [['availabilityArray'], 'each', 'rule' => ['integer'], 'on' => 'default'],
            ['zipCode', 'match', 'pattern' => '/^\d{5}$/'],
            [
                'zipCode',
                'exist',
                'targetClass' => Zipcode::class,
                'targetAttribute' => 'code',
                'message' => 'Please use United States Postal Code'
            ],
            ['forceSendingNotification', 'boolean'],
            ['files', 'each', 'rule' => ['integer']],
            [
                'files',
                function ($attribute, $params) {
                    if (count($this->$attribute) > JobHelper::getMaxFiles()) {
                        $this->addError($attribute, 'Max files limit exceeded');
                    }
                },
            ],
        ];

        if (!$this->isNeedDisplayGender) {
            $rules[] = [['!gender'], 'safe'];
        }
        return $rules;
    }

    /**
     * @return array
     */
    public function getGrade()
    {
        return ConstantsHelper::schoolGradeLevel();
    }

    public function getIsAutomatchJob(): bool
    {
        return $this->isAutomatchEnabled || ($this->getAutomatchHistory()->exists());
    }

    /**
     * @return array
     */
    public function getLessonOccur()
    {
        return ConstantsHelper::lessonOccur();
    }

    /**
     * @return array
     */
    public static function getGenderArray()
    {
        return ConstantsHelper::genderJob();
    }

    /**
     * @return array
     * @deprecated
     * Left for backwords compatibility
     */
    public function getStLesson()
    {
        return self::getStartLessonTime();
    }

    /**
     * @return array
     * @deprecated
     * Left for backwords compatibility
     */
    public function getLesson()
    {
        return self::getLessonOccur();
    }

    public function fields()
    {
        return array_merge(parent::fields(), ['automatchTimerEnd', 'isAutomatchJob', 'automatchRate']);
    }

    public function getAutomatchTimerEnd(): ?int
    {
        if ($this->automatchJobId) {
            $q = (new Query())
                ->select('pushed_at')
                ->from('yii_queue')
                ->andWhere(['id' => $this->automatchJobId]);

            $pushedAt = (int)$q->scalar();
            $diff = ($pushedAt + Automatch::QUEUE_DELAY) - time();
            return ($diff < 0) ? 0 : $diff;
        }

        return 0;
    }

    /**
     * @return array
     */
    public static function getStartLessonTime()
    {
        return ConstantsHelper::startLessonTime();
    }

    public static function autogenerateJob($subjects, $zipCode, $description, $accountId = null)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $job = new self([
                'hourlyRateFrom' => 20,
                'hourlyRateTo' => 250,
                'availability' => 2097151, //int all days
                'studentGrade' => 1, //Elementary
                'lessonOccur' => 3, //Tutor's location
                'gender' => 'B',
                'startLesson' => 4, //This month
                'autogenerate' => 1,
                'status' => self::UNPUBLISH //unpublish
            ]);
            $job->description = $description;
            $job->subjects = $subjects;
            $job->zipCode = $zipCode;
            $job->newJob = self::NEW_JOB;
            $job->scenario = 'autogenerate';
            $job->accountId = $accountId;
            if (!$job->save()) {
                throw new \Exception('Job save is failed');
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'accountId' => 'Account ID',
            'studentGrade' => 'Student Grade',
            'lessonOccur' => 'Lesson Occur',
            'gender' => 'Gender',
            'age' => 'Age',
            'hourlyRate' => 'Hourly Rate',
            'startLesson' => 'Start Lesson',
            'availabilityArray' => 'Availability',
            'description' => 'Description',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * @return array[] SubjectOrCategory
     */
    public function getSubjectsOrCategories()
    {
        $resultArray = [];
        $jobSubjectsOrCategory = JobSubjectOrCategory::find()->andWhere(['jobId' => $this->id])->all();
        if (!empty($jobSubjectsOrCategory)) {
            foreach ($jobSubjectsOrCategory as $jobSubjectOrCategory) {
                $subject = $jobSubjectOrCategory->getSubjectOrCategory();
                if ($subject) {
                    $resultArray[] = $subject;
                }
            }
        }
        return $resultArray;
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
            'biteString' => [
                'class' => 'common\components\behaviors\BiteStringBehavior',
                'attributeToSave' => 'availability',
                'attributeToDisplay' => 'availabilityArray',
                'biteStringLength' => AvailabilityHelper::BITE_STRING_LENGTH
            ],
        ];
    }

    public function hasAutoMatchSubjects(): bool
    {
        $autoMatchSubjectIds = AutomatchSubject::allIds();
        foreach ($this->subjects as $subjectId) {
            if (in_array($subjectId, $autoMatchSubjectIds)) {
                $hasAutomatchSubject = true;
            }
        }
        return  $hasAutomatchSubject ?? false;
    }

    public function checkIsAutomatchJob()
    {
        return $this->isB2bJob()
            && $this->hasAutoMatchSubjects()
            && $this->isOnline
        ;
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            if (is_null($this->accountId)) {
                $this->accountId = Yii::$app->user->id;
            }

            $this->newJob = 1;
            $this->isAutomatchEnabled = $this->checkIsAutomatchJob();
        }

        //if only isAutomatchEnabled flag changed - detach behavior (do not change updatedAt field).
        if (
            $this->isAttributeChanged('isAutomatchEnabled') && (count($this->dirtyAttributes) == 1)
        ) {
                $this->detachBehavior('timestamp');
        }

        //applicant limit of new job posted notifications depends on count manual launches
        //first time it increases to 10 applicants, second time to 15, third to 20 ect.
        if (($this->isAttributeChanged('forceSendingNotification')) && $this->forceSendingNotification) {
            //go to next notification cycle
            // (to increment total applicants limit before stop sending new job posted notifications)
            $this->notificationCycle += 1;
        }

        if ($this->isAttributeChanged('forceSendingNotification') && !$this->forceSendingNotification) {
            //need to return previous applicant limit, so when sending process will lunch again -
            // application limit will be correct
            $this->notificationCycle -= 1;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return string
     */
    public function getRelativeTimePosting()
    {
        //TODO change to createdAt ?????
        return Yii::$app->formatter->format($this->updatedAt, 'relativeTime');
    }

    public function isB2bJob()
    {
        return $this->account->isPatient();
    }

    public function getTimePosting()
    {
        $date1 = new \DateTime("now");
        $date2 = new \DateTime($this->updatedAt);

        $hour = $date2->diff($date1)->h;
        $day = $date2->diff($date1)->d;
        $month = $date2->diff($date1)->m;
        $minute = $date2->diff($date1)->i;
        $time = '';
        if ($month) {
            $time .= $month . ' month ';
        }
        if ($day) {
            $time .= $day . ' days ';
        }
        if ($hour) {
            $time .= $hour . ' hours ';
        }
        $time .= $minute . ' minutes';
        return $time;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZipCodeItem()
    {
        $model = Location::getModelZipCode();
        return $this->hasOne($model::className(), ['code' => 'zipCode']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAutomatchHistory()
    {
        return $this->hasOne(AutomatchHistory::class, ['jobId' => 'id']);
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
    public function getJobApply()
    {
        return $this->hasMany(JobApply::class, ['jobId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLessons()
    {
        return $this->hasMany(Lesson::class, ['jobId' => 'id']);
    }

    public function getAutomatchRate()
    {
        return (round($this->getClientBillRate() * 0.4));
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobOffers()
    {
        $accountModule = Yii::$app->getModuleAccount();
        $jobOffer = $accountModule->modelStatic('JobOffer');
        return $this->hasMany($jobOffer, ['jobId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLatestJobOffer()
    {
        $accountModule = Yii::$app->getModuleAccount();
        $jobOffer = $accountModule->modelStatic('JobOffer');
        return $this->hasOne($jobOffer, ['jobId' => 'id'])->addOrderBy(['createdAt' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApplicants()
    {
        return $this->hasMany(Account::class, ['id' => 'accountId'])->via('jobApply');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobSubjects()
    {
        return $this->hasMany(JobSubject::class, ['jobId' => 'id']);
    }

    /**
     * select ids of SUBJECTS that related to job
     * @return array
     */
    public function getJobSubjectsIdsSubject()
    {
        $models = $this->getJobSubjects()->andWhere(['isCategory' => 0])->asArray()->all();
        return ArrayHelper::getColumn($models, 'subjectId');
    }

    /**
     * select ids of CATEGORIES that related to job
     * @return array
     */
    public function getJobSubjectsIdsCategory()
    {
        $models = $this->getJobSubjects()->andWhere(['isCategory' => 1])->asArray()->all();
        return ArrayHelper::getColumn($models, 'subjectId');
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubjects()
    {
        return $this->hasMany(Subject::class, ['id' => 'subjectId'])
            ->viaTable(JobSubject::tableName(), ['jobId' => 'id']);
    }

    public function getCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'subjectId'])
            ->viaTable(JobSubject::tableName(), ['jobId' => 'id']);
    }

    /**
     * Convert weekdays format
     *
     * @param string $day
     * @return bool|string
     */
    public static function convertWeekday($day)
    {
        return AvailabilityHelper::convertWeekDay($day);
    }


    public function getAvailabilityData()
    {
        return AvailabilityHelper::$availabilityData;
    }

    /**
     * @return array
     * @todo Need refactoring
     */
    public function getAvailabilityDataMobile()
    {
        return AvailabilityHelper::$availabilityDataMobile;
    }

    /**
     * @return array
     * @todo Need refactoring
     */
    public function getAvailabilityMobile()
    {
        return AvailabilityHelper::mobileData($this->availabilityArray);
    }

    /**
     * @return bool
     */
    public function getIsOnline(): bool
    {
        return $this->lessonOccur === static::LESSON_OCCUR_ONLINE;
    }

    public function getStatusText()
    {
        return isset($this->statusTextArray[$this->status]) ?
            $this->statusTextArray[$this->status] :
            $this->status;
    }

    public function getStudentGradeText()
    {
        return isset($this->grade[$this->studentGrade]) ?
            $this->grade[$this->studentGrade] :
            $this->studentGrade;
    }

    public function getLessonOccurText()
    {
        return isset(self::getLessonOccur()[$this->lessonOccur]) ?
            self::getLessonOccur()[$this->lessonOccur] :
            $this->lessonOccur;
    }

    public function getGenderText()
    {
        return isset($this->genderArray[$this->gender]) ?
            $this->genderArray[$this->gender] :
            $this->gender;
    }

    public function getStartLessonText()
    {
        return isset(self::getStartLessonTime()[$this->startLesson]) ?
            self::getStartLessonTime()[$this->startLesson] :
            '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        $model = Location::getModelCity();
        return $this->hasOne($model::className(), ['id' => 'cityId'])
            ->viaTable('{{%location_zipcode}}', ['code' => 'zipCode']);
    }

    /**
     * @return string
     */
    public function getCityName(): string
    {
        return $this->lessonOccur == static::LESSON_OCCUR_ONLINE ? 'Online' : ($this->city->name ?? '');
    }

    /**
     * @return mixed
     */
    public function getJobPostDatetimeWithSlashes()
    {
        return Yii::$app->formatter->getDatetimeWithSlashes(strtotime($this->updatedAt));
    }

    public function getFullName()
    {
        return $this->getNameWithLocationAndSubject();
    }

    /**
     * Get name with location and subjects
     * @return string
     */
    public function getNameWithLocationAndSubject()
    {
        $subject = $this->getSubjectsOrCategories()[0] ?? null;
        $locationCityName = $this->getCityName();
        return $locationCityName . ($subject ? ' ' . $subject->name : ' ') . ' Tutoring Job';
    }

    /**
     * Get name with location and subjects
     * @return string
     */
    public function getShortNameWithLocationAndSubject($length)
    {
        return StringHelper::truncate($this->getNameWithLocationAndSubject(), $length);
    }

    public function getName($countLetters = 20)
    {
        return $this->getShortNameWithLocationAndSubject($countLetters);
    }

    public function descriptionTruncate($seemore = '...')
    {
        return mb_strlen($this->description) > static::MOBILE_COUNT_LETTER_DESCRIPTION ?
            StringHelper::truncate(
                $this->description,
                static::MOBILE_COUNT_LETTER_DESCRIPTION,
                $seemore
            ) :
            $this->description;
    }

    // Proxy-ing default methods as custom ones to allow getting suspended jobs too
    public static function findOneWithoutRestrictions($condition)
    {
        return self::findWithoutRestrictions()->andWhere(['id' => $condition])->one();
    }

    public static function findWithoutRestrictions()
    {
        return parent::find();
    }

    public static function findByConditionWithoutRestrictions($condition)
    {
        return self::findWithoutRestrictions()->andWhere($condition);
    }

    public static function findBySqlWithoutRestrictions($sql, $params = [])
    {
        $query = self::findWithoutRestrictions();
        $query->sql = $sql;

        return $query->params($params);
    }

    public static function findAllWithoutRestrictions($condition)
    {
        return self::findByConditionWithoutRestrictions($condition)->all();
    }


    protected static function addNonBlockedCondition($query)
    {
//        return $query->andWhere(['!=', 'block', 1]);
    }

    protected static function addNonSuspendedCondition($query)
    {
//        return $query->andWhere(['suspended' => false]);
    }


    /**
     * @inheritdoc
     */
    public static function findOne($condition)
    {
        $query = parent::findByCondition($condition);
        static::addNonBlockedCondition($query);
        static::addNonSuspendedCondition($query);
        return $query->one();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        $query = parent::find();
        static::addNonBlockedCondition($query);
        static::addNonSuspendedCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findByCondition($condition)
    {
        $query = parent::findByCondition($condition);
        static::addNonBlockedCondition($query);
        static::addNonSuspendedCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findBySql($sql, $params = [])
    {
        $query = parent::findBySql($sql, $params);
        static::addNonBlockedCondition($query);
        static::addNonSuspendedCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findAll($condition)
    {
        $query = parent::findByCondition($condition);
        static::addNonBlockedCondition($query);
        static::addNonSuspendedCondition($query);
        return $query->all();
    }

    public function delete()
    {
        $this->close = true;
        $this->closeDate = new Expression('NOW()');
        return $this->save(true, ['close', 'closeDate', 'updatedAt']);
    }

    protected function getNotDeclinedApplies(): array
    {
        $applies = $this->getJobApply()->indexBy('id')->all();
        $declinedTutorIds = $this->getDeclinedJobHires()->select('tutorId')->column();

        /**
         * @var JobApply $apply
         */
        foreach ($applies as $applyId => $apply) {
            //skip declined tutors
            if (in_array($apply->accountId, $declinedTutorIds)) {
                unset($applies[$applyId]);
            }
        }
        return $applies;
    }

    public function autoMatch($zeroDelay = false)
    {
        if ($this->automatchJobId) {
            Yii::$app->db->createCommand('DELETE FROM yii_queue WHERE id = ' . $this->automatchJobId)->execute();
        }
        $queueJobId = QueueHelper::automatchJob($this->id, $zeroDelay);
        Job::updateAll(['automatchJobId' => $queueJobId], ['id' => $this->id]);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->subjects) {
            // Update job subjects only if list of subject IDs provided
            $this->updateJobSubjects();
        }

        /**
         * @var Module $accountModule
         */
        $accountModule = Yii::$app->getModule('account');

        if ($insert) {
            $accountModule->eventNewJob($this);
            $this->processEventJobPostingOlder3Day();

            if (!empty($this->files)) {
                $this->processFiles();
            }

            if ($this->isAutomatchEnabled) {
                $this->autoMatch();
            }
        } else {
            if (isset($changedAttributes['isAutomatchEnabled']) && $this->isAutomatchEnabled) {
                $this->autoMatch();
            }

            if (array_key_exists('billRate', $changedAttributes)) {
                $oldValue = $changedAttributes['billRate'] ?? null;
                if (empty($oldValue)) {
                    //use client rate as old value
                    $oldValue = $this->account->rate->hourlyRate ?? 0;
                }
                $newValue = $this->getClientBillRate();

                if ((int)$oldValue != (int)$newValue) {
                    $logInstance = new JobRateChangeLog();
                    $logInstance->madeByAccount = \Yii::$app->user->identity ?? null;
                    $logInstance->objectId = $this->id;
                    $logInstance->oldValue = [$oldValue];
                    $logInstance->newValue = [$newValue];
                    $logInstance->save(false);
                }
            }
        }

        if (!$insert && isset($changedAttributes['close'])) {
            if ($this->close) {
                $accountModule->eventJobClosed($this);
            } else {
                $accountModule->eventJobOpened($this);
            }
        }
    }

    public function getChangeList()
    {
        return $this->hasMany(ChangeLog::class, ['objectId' => 'id'])
            ->onCondition(['objectType' => ChangeLog::OBJECT_TYPE_JOB]);
    }

    /**
     * @throws \Exception
     */
    private function processEventJobPostingOlder3Day()
    {

        $timeToProcess = (new \DateTime())->modify("+3 days")->getTimestamp() - (new \DateTime())->getTimestamp();
        Yii::$app->yiiQueue
            ->delay($timeToProcess)
            ->push(new JobPostingOlderThan3DaysJob([
                'jobId' => $this->id,
            ]));
    }

    /**
     *
     */
    private function processFiles()
    {
        foreach ($this->files as $file) {
            $uploadedFile = FileModel::findOne($file);
            if ($uploadedFile) {
                $uploadedFile->status = FileModel::STATUS_ATTACHED;
                $uploadedFile->job_id = $this->id;
                $uploadedFile->save(false);
            }
        }
    }

    public function getAllJobSubjectsOrCategories()
    {
        return JobSubjectOrCategory::find()->andWhere(['jobId' => $this->id])->all();
    }

    protected function updateJobSubjects()
    {
        $jobSubjectsOrCategories = $this->getAllJobSubjectsOrCategories();
        if (!empty($jobSubjectsOrCategories)) {
            foreach ($jobSubjectsOrCategories as $model) {
                $model->delete();
            }
        }
        foreach ($this->subjects as $subjectOrCategoryId) {
            $jobSubjectOrCategory = new JobSubjectOrCategory();
            $jobSubjectOrCategory->subjectId = (int)$subjectOrCategoryId;
            $jobSubjectOrCategory->isCategory = SubjectOrCategory::isIdOfCategory($subjectOrCategoryId);
            $jobSubjectOrCategory->jobId = $this->id;
            $jobSubjectOrCategory->save(false);
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNewJobPostedNotificationEvents()
    {
        return $this->hasMany(ProcessedEvent::class, ['jobId' => 'id'])
            ->andOnCondition([
                ProcessedEvent::tableName() . '.type' => ProcessedEvent::TYPE_NEW_JOB_POSTED_NOTIFICATIONS_PROCESSED,
            ]);
    }

    /**
     * @return ActiveQuery
     */
    public function getAttachedFiles()
    {
        return $this->hasMany(FileModel::class, ['job_id' => 'id'])
            ->andWhere([FileModel::tableName() . '.status' => FileModel::STATUS_ATTACHED]);
    }

    /**
     * @return int
     */
    public function getCountProcessedBatches(): int
    {
        return (int)$this->getNewJobPostedNotificationEvents()->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobHires()
    {
        return $this->hasMany(JobHire::class, ['jobId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHiredJobHires()
    {
        return $this->getJobHires()->andOnCondition([JobHire::tableName() . '.status' => JobHire::STATUS_HIRED]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeclinedJobHires()
    {
        return $this->getJobHires()->andOnCondition([JobHire::tableName() . '.status' => [JobHire::STATUS_DECLINED_BY_COMPANY, JobHire::STATUS_DECLINED_BY_TUTOR]]);
    }

    /**
     * @param null $tutorId
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJobHire($tutorId = null)
    {
        if (!$tutorId) {
            $tutorId = Yii::$app->user->id;
        }
        $jobHireClass = Yii::$app->getModuleAccount()->modelStatic('JobHire');
        return $this->hasOne($jobHireClass, ['jobId' => 'id'])
            ->andOnCondition([$jobHireClass::tableName() . '.tutorId' => $tutorId]);
    }

    public function getLatestCompanyJobOffer($tutorId = null)
    {
        return $this->getLatestJobOfferByType(JobOffer::TYPE_OFFERED_BY_COMPANY, $tutorId);
    }

    public function getLatestTutorJobOffer($tutorId = null)
    {
        return $this->getLatestJobOfferByType(JobOffer::TYPE_OFFERED_BY_TUTOR, $tutorId);
    }

    public function getLatestJobOfferByType($type, $tutorId = null)
    {
        if (!$tutorId) {
            $tutorId = Yii::$app->user->id;
        }
        return $this->getLatestJobOffer()
            ->andOnCondition([JobOffer::tableName() . '.type' => $type])
            ->andOnCondition([JobOffer::tableName() . '.tutorId' => $tutorId]);
    }

    public function getClientBillRate()
    {
        return (int)$this->billRate ?: $this->account->rate->hourlyRate ?? null;
    }

    /**
     * return array of related subjects with subjects from selected categories
     * @return array
     */
    public function getRelatedSubjectsWithSubjectsFromCategories()
    {
        /* $allRelatedSubjects - save result of function to this variable to prevent extra queries */
        if (empty($this->allRelatedSubjects)) {
            $subjectIds = JobSubjectOrCategory::find()->andWhere(['isCategory' => 0, 'jobId' => $this->id])->asArray()->all();
            $categoriesIds = JobSubjectOrCategory::find()->andWhere(['isCategory' => 1, 'jobId' => $this->id])->asArray()->all();


            $subjectIds = ArrayHelper::getColumn($subjectIds, 'subjectId');
            $categoriesIds = ArrayHelper::getColumn($categoriesIds, 'subjectId');

            $subjects = Subject::find()->andWhere(['in', 'id', $subjectIds]);
            if (!empty($subjectsIdFromJob)) {
                $subjects = $subjects->andWhere(['in', 'id', $subjectsIdFromJob]);
            }
            $subjects = $subjects->indexBy('id')->all();


            if (!empty($categoriesIds) || !empty($categoryIdsFromJob)) {
                $subjectsFromCategories = Subject::find();
                $subjectsFromCategories->joinWith('category');
                if (!empty($categoryIdsFromJob)) {
                    $subjectsFromCategories = $subjectsFromCategories->andWhere([
                        Category::tableName() . '.id' => $categoryIdsFromJob,
                    ]);
                }
                if (!empty($categoriesIds)) {
                    $subjectsFromCategories = $subjectsFromCategories->andWhere([Category::tableName() . '.id' => $categoriesIds
                    ]);
                }
                $subjectsFromCategories = $subjectsFromCategories->indexBy('id')->all();
            }
            $this->allRelatedSubjects = ($subjectsFromCategories ?? []) + $subjects;
        }
        return $this->allRelatedSubjects;
    }

    public function getRelatedSubjectsWithSubjectsFromCategoriesNamesArray()
    {
        $names = [];
        $models = $this->getRelatedSubjectsWithSubjectsFromCategories();
        if ($models) {
            foreach ($models as $model) {
                $names[] = $model->name;
            }
        }
        return $names;
    }

    public function getSubjectOrCategoryNamesArray()
    {
        $names = [];
        $models = $this->getSubjectsOrCategories();
        if ($models) {
            foreach ($models as $model) {
                $names[] = $model->getName();
            }
        }
        return $names;
    }

    public function getSubjectsOrCategoriesIndexedById()
    {
        $resultArray = [];
        $jobSubjectsOrCategory = JobSubjectOrCategory::find()->andWhere(['jobId' => $this->id])->all();
        if (!empty($jobSubjectsOrCategory)) {
            foreach ($jobSubjectsOrCategory as $jobSubjectOrCategory) {
                $model = $jobSubjectOrCategory->getSubjectOrCategory();
                $resultArray[$model->getId()] = $model;
            }
        }
        return $resultArray;
    }

    public function getPublicLink()
    {
        return Url::getFrontendUrl('/apply-job/') . $this->id . '/';
    }

    /**
     * @return string
     */
    public function getDisplayedDescription()
    {
        return nl2br($this->description);
    }

    /**
     * @return string
     */
    public function getXmlDescription(): string
    {
        //replace double \r\n to <br> looking for https://impm.pro/browse/HEYTUTOR-4527
        return str_replace("\n\n", "<br/>", $this->description);
    }

    /**
     * @param int $tutorAccountId
     * @param int $studentAccountId
     * @param string|null $jobSearchName
     * @return \yii\db\ActiveQuery
     */
    public static function getStudentJobsQuery(int $tutorAccountId, int $studentAccountId, $jobSearchName = null)
    {
        $query = static::find()
            ->joinWith('hiredJobHires')
            ->andWhere([JobHire::tableName() . '.tutorId' => $tutorAccountId])
            ->andWhere(['not', [JobHire::tableName() . '.id' => null]])
            ->andWhere([Job::tableName() . '.accountId' => $studentAccountId]);

        if (is_string($jobSearchName) && !empty($jobSearchName)) {
            $query->joinWith('subjects')
                ->andFilterWhere(['like', Subject::tableName() . '.name', $jobSearchName]);
        }

        return $query;
    }

    public function getAppliedStatus($tutorId = null)
    {
        $text = '';
        /**
         * @var JobHire $jobHire
         */
        $jobHire = $this->getJobHire($tutorId)->one();
        if (!empty($jobHire)) {
            switch ($jobHire->status) {
                case JobHire::STATUS_HIRED:
                    $text = "Hired on $ {$this->jobHire->formattedPrice}  / Hour";
                    break;
                case JobHire::STATUS_DECLINED_BY_TUTOR:
                    $text = 'Declined by you';
                    break;
                case JobHire::STATUS_DECLINED_BY_COMPANY:
                    $text = 'Application declined';
                    break;
                case JobHire::STATUS_CLOSED_BY_COMPANY:
                    $text = 'Closed Job';
                    break;
            }
        } else {
            $applied = JobApply::find()
                ->andWhere(['jobId' => $this->id])
                ->andWhere(['accountId' => $tutorId ?? Yii::$app->user->id])
                ->exists();
            if ($applied) {
                $text = 'applied';
            }
        }
        return $text;
    }

    public function getCoordinates(bool $withOffset = true, float $radius = .75, float $radiusWithIn = .75)
    {
        $coordinates = [
            'latitude' => (double)$this->account->profile->latitude,
            'longitude' => (double)$this->account->profile->longitude,
            'radius' => $radius,
        ];
        if (!$withOffset) {
            return $coordinates;
        }

        $even = $this->id % 2 === 0;
        $coordinatesForRadius = $this->getCoordinatesForRadius(
            $coordinates['latitude'],
            $coordinates['longitude'],
            ($radiusWithIn < $radius) ? $radius : $radiusWithIn
        );

        $coordinates['latitude'] = $coordinatesForRadius[$even ? 'latitudeMin' : 'latitudeMax'];
        $coordinates['longitude'] = $coordinatesForRadius[!$even ? 'longitudeMin' : 'longitudeMax'];

        return $coordinates;
    }

    public function tutorNotifiedAboutNewJob(int $tutorId, array $data, int $totalScore = null): bool
    {
        $processedEvent = new ProcessedEvent();
        $processedEvent->jobId = $this->id;
        $processedEvent->accountId = $tutorId;
        $processedEvent->type = ProcessedEvent::TYPE_TUTOR_NOTIFIED_ABOUT_NEW_JOB;
        $processedEvent->data = $data;
        $processedEvent->totalScore = $totalScore;
        return $processedEvent->save(false);
    }

    /**
     * @param int $value
     * @return int
     * @throws \yii\db\Exception
     */
    public function updateCountNotificationCounter(int $value): int
    {
        $counterExpression = new Expression(
            "IF (ISNULL([[countNotification]]), 1, [[countNotification]]+:bp0)",
            [":bp0" => $value]
        );
        $command = static::getDb()->createCommand();
        $command->update(static::tableName(), ['countNotification' => $counterExpression], ['id' => $this->id], []);
        return $command->execute();
    }

    public function getTutorNotifiedCondition(): ProcessedEventQuery
    {
        return ProcessedEvent::find()
            ->job($this->id)
            ->tutorNotifiedAboutNewJob()
            ->select('accountId');
    }

    /**
     * @return array
     */
    public function getIdsTutorsNotified(): array
    {
        return $this->getTutorNotifiedCondition()->column();
    }

    public function isTutorNotified(int $tutorId): bool
    {
        return $this->getTutorNotifiedCondition()->accountId($tutorId)->exists();
    }

    protected function getCoordinatesForRadius(float $latitude, float $longitude, float $radius)
    {
        $longitudeMin = $longitude - $radius / abs(cos(deg2rad($latitude)) * 180);
        $longitudeMax = $longitude + $radius / abs(cos(deg2rad($latitude)) * 180);
        $latitudeMin = $latitude - ($radius / 180);
        $latitudeMax = $latitude + ($radius / 180);

        return compact('longitudeMin', 'longitudeMax', 'latitudeMin', 'latitudeMax');
    }

    public static function repostJob(int $jobId)
    {
        $jobModel = static::findOne($jobId);

        if (!$jobModel) {
            throw new NotFoundHttpException('No such job found.');
        }
        if (!$jobModel->isJobClose()) {
            throw new \InvalidArgumentException('Job cannot be reposted. First you need to close the job');
        }
        if ($jobModel->isJobAlreadyReposted()) {
            throw new \InvalidArgumentException('Sorry, this job has already been reposted');
        }
        $dbTransaction = Yii::$app->db->beginTransaction();
        $repostJob = new static();
        $repostJob->subjects = $jobModel->getJobSubjects()->select(['subjectId'])->column();
        $repostJob->originJobId = $jobModel->originJobId ? $jobModel->originJobId : $jobModel->id;
        $repostJob->repostedJobId = $jobModel->id;
        $repostJob->status = MainJob::NEW_JOB;
        $repostJob->accountId = $jobModel->accountId;
        $repostJob->studentGrade = $jobModel->studentGrade;
        $repostJob->lessonOccur = $jobModel->lessonOccur;
        $repostJob->gender = 'B';
        $repostJob->ageFrom = $jobModel->ageFrom;
        $repostJob->ageTo = $jobModel->ageTo;
        $repostJob->hourlyRateFrom = $jobModel->hourlyRateFrom;
        $repostJob->hourlyRateTo = $jobModel->hourlyRateTo;
        $repostJob->startLesson = $jobModel->startLesson;
        $repostJob->availability = $jobModel->availability;
        $repostJob->description = $jobModel->description;
        $repostJob->zipCode = $jobModel->zipCode;
        $repostJob->billRate = $jobModel->billRate;
        $repostJob->isAutomatchEnabled = $jobModel->isAutomatchEnabled;
        $repostJob->automatchJobId = $jobModel->automatchJobId;

        if (!$repostJob->save()) {
            $dbTransaction->rollBack();
            return $repostJob;
        }

        $jobModel->isRematchClose = true;
        if (!$jobModel->save(false)) {
            $dbTransaction->rollBack();
            return $jobModel;
        }
        $tutorHired = JobHire::find()
            ->select('tutorId')
            ->andWhere(['status' => JobHire::STATUS_HIRED])
            ->andWhere(['jobId' => $jobModel->id])
            ->all();

        if ($tutorHired) {
            $batchData = array_map(function ($item) use ($repostJob) {
                return [
                    $repostJob->originJobId,
                    $repostJob->repostedJobId,
                    $item['tutorId'],
                    (new \DateTime())->format('Y-m-d H:i:s'),
                    (new \DateTime())->format('Y-m-d H:i:s'),
                ];
            }, ArrayHelper::toArray($tutorHired));

            $countOfAddedTutors = Yii::$app->db->createCommand()
                ->batchInsert(
                    IgnoredTutorsJob::tableName(),
                    ['originJobId', 'jobId', 'tutorId', 'createdAt', 'updatedAt'],
                    $batchData
                )
                ->execute();
            if ($countOfAddedTutors !== count($tutorHired)) {
                $dbTransaction->rollBack();
                throw new BadRequestHttpException('Bad request');
            }
        }
        $dbTransaction->commit();
        $jobModel->refresh();
        $jobModel->load(Yii::$app->getRequest()->getQueryParams(), '');
        return $jobModel;
    }

    /**
     * @return bool
     */
    public function isJobClose(): bool
    {
        return (bool)$this->close;
    }

    /**
     * @return bool
     */
    public function isJobAlreadyReposted(): bool
    {
        return (bool)$this->close === true && (bool)$this->isRematchClose === true;
    }
}

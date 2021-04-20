<?php

namespace modules\account\models;

use common\components\ActiveQuery;
use common\components\ActiveRecord;
use common\helpers\QueueHelper;
use common\models\Review;
use DateTime;
use modules\account\helpers\EventHelper;
use modules\account\helpers\SubjectHelper;
use modules\account\helpers\Timezone;
use modules\account\models\api\AccountClient;
use modules\account\models\query\LessonQuery;
use modules\payment\models\Transaction;
use Yii;

/**
 * This is the model class for table "{{%lesson}}".
 *
 * @property integer $id
 * @property integer $studentId
 * @property integer $tutorId
 * @property integer $jobId
 * @property string $fromDate
 * @property string $toDate
 * @property integer $subjectId
 * @property integer $hourlyRate
 * @property string $paymentDate
 * @property integer $status
 * @property double $amount
 * @property double $fee
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property string $statusString
 *
 * @property Account $student
 * @property Account $tutor
 * @property Review $review
 * @property Job $job
 * @property Transaction $lastTransaction
 * @property-read Subject $subject
 * @property double $calculatedClientPrice
 */
class Lesson extends ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_PENDING = 1;
    const STATUS_CHARGE = 2;
    const STATUS_REFUND = 3;
    const STATUS_REJECTED = 4;

    //list of status labels related to current user role, defined in getStatusLabel() method
    protected $statusLabels;

    const INTERNAL_DATE_FORMAT = 'Y-m-d H:i';
    const INCOMING_DATE_FORMAT = 'm/d/Y H:i';

    const DATE_FROM_GREATER_THAN_DATE_TO_ERROR_MESSAGE = 'Lessons must be submitted after they have already taken place. Students are billed after lessons, not before.';
    const DATE_MIN_DATE_ERROR_MESSAGE = 'Date should not be more than 2 months from today’s date.';

    const CLIENT_BALANCE_MINIMUM = -100;

    public static function getClientBalanceErrorMessage(): string
    {
        return 'We’re sorry, your lesson could not be logged. Please call us at '
            . \Yii::$app->phoneNumber->getDefaultTutorPhoneNumberFormatted() . ' or email support@winitclinic.com.';
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%lesson}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $allowedTimeErrorMessageWithStudentName = 'Whoops! This could be a duplicate lesson. You have already entered a lesson with {studentName} during the time submitted. Please visit lessons & earnings for more information.';
        $allowedTimeErrorMessage = 'Whoops! This student has already scheduled lesson for this period.';
        return [
            [['studentId', 'fromDate', 'toDate', 'subjectId'], 'required'],
            [['studentId', 'subjectId', 'jobId'], 'integer'],
            [
                'jobId',
                'required',
                'message' => 'Please select a job to proceed further',
                'when' => function () {
                    return $this->student->isPatient();
                }
            ],
            [['convertedFromDate', 'convertedToDate'], 'safe'],
            [
                ['fromDate'], 'filter', 'filter' => [Timezone::class, 'staticConvertToServerTimeZone'], 'when' => function ($model) {
                    $isChanged = $this->isAttributeChanged('fromDate');
                    //if attribute didn't changed it still must be converted, when it changed - it will be converted on
                    // '\modules\account\helpers\Timezone', 'convertToServerTimeZone'
                    if (!$isChanged) {
                        $this->fromDate = $this->convertToIncomingFormat($this->fromDate);
                    }
                    return $isChanged;
                }
            ],
            [
                ['toDate'], 'filter', 'filter' => [Timezone::class, 'staticConvertToServerTimeZone'], 'when' => function ($model) {
                    $isChanged = $this->isAttributeChanged('toDate');
                    //if attribute didn't changed it still must be converted, when it changed - it will be converted on '\modules\account\helpers\Timezone', 'convertToServerTimeZone'
                    if (!$isChanged) {
                        $this->toDate = $this->convertToIncomingFormat($this->toDate);
                    }
                    return $isChanged;
                }
            ],
            ['fromDate', 'date', 'type' => 'datetime', 'format' => 'php:' . self::INCOMING_DATE_FORMAT, 'timestampAttribute' => 'fromDate', 'timestampAttributeFormat' => 'php:' . self::INTERNAL_DATE_FORMAT],
            ['toDate', 'date', 'type' => 'datetime', 'format' => 'php:'  . self::INCOMING_DATE_FORMAT, 'timestampAttribute' => 'toDate', 'timestampAttributeFormat' => 'php:' . self::INTERNAL_DATE_FORMAT],
            ['toDate', 'validateToDate', 'skipOnError' => true],
            ['toDate', 'previousMonthValidator', 'skipOnError' => true],
            [['fromDate', 'toDate'], 'validateAllowedTime', 'skipOnError' => true, 'params' => ['message' => $allowedTimeErrorMessage, 'messageWithStudentName' => $allowedTimeErrorMessageWithStudentName]],
            ['fromDate', 'validateLongLesson', 'skipOnError' => true, 'params' => ['message' => $allowedTimeErrorMessage, 'messageWithStudentName' => $allowedTimeErrorMessageWithStudentName]],
            [['studentId'], 'exist', 'skipOnError' => true, 'targetClass' => Account::class, 'targetAttribute' => ['studentId' => 'id']],
            [
                ['jobId'], 'exist', 'skipOnError' => true, 'targetClass' => Job::class, 'targetAttribute' => ['jobId' => 'id'], 'filter' => function ($query) {
                    /**
                    * @var $query ActiveQuery
                    */
                    $query->andWhere(['accountId' => $this->studentId]);
                }
            ],
            [['jobId'], 'checkJob', 'skipOnError' => true],
            [['subjectId'], 'checkSubject', 'skipOnError' => true],
            [['studentId'], 'clientBalanceValidator',],
            [
                ['tutorId'],
                'tutorCapabilitiesValidator',
                'when' => function ($model) {
                    return $model->isNewRecord;
                }
            ]
        ];
    }

    public function tutorCapabilitiesValidator()
    {
        $paymentAccount = $this->tutor->paymentAccount ?? null;
        if (empty($paymentAccount)) {
            $this->addError('tutorId', 'Invalid payment account.');
        }

        if ($paymentAccount->updatesRequired) {
            $this->addError('tutorId', ' Sorry, we are unable to submit this lesson due to Stripe verification. Please open your payment profile settings to proceed with identity verification');
        }
    }
    /**
     * Company client should have enough funds on balance to create lesson.
     */
    public function clientBalanceValidator(): void
    {
        if ($this->student->isPatient()) {
            $currentStudentBalance = $this->student->clientStatistic->balance ?? null;
            if (
                (
                    (is_null($currentStudentBalance))
                    || empty($this->job)
                    || (($currentStudentBalance - $this->calculateClientPrice()) < static::CLIENT_BALANCE_MINIMUM)
                )
                //disable this check for PAYG clients
                && !
                (
                    !empty($this->student->paymentCustomer)
                    && $this->student->paymentCustomer->isPAYG()
                )
            ) {
                $this->addError('studentId', static::getClientBalanceErrorMessage());
            }
        }
    }


    public function checkJob($attribute, $params)
    {
        if (
            $this->student->isPatient()
        ) {
            $query = $this->job->getJobHires()
                ->andWhere(['tutorId' => $this->tutorId])
                ->andWhere(['not', ['status' => JobHire::$declineStatuses]]);
            if (!(clone $query)->exists()) {
                $this->addError($attribute, 'To submit this lesson, you have to be hired by company.');
                return;
            }
            //one tutor cold be related only to one job hire with this job
            /**
             * @var JobHire $jobHire
             */
            $jobHire = (clone $query)->limit(1)->one();
            if ($jobHire->isStatusClosedByCompany()) {
                $this->addError($attribute, 'Sorry, this job is on hold. Please use an active job to submit a lesson.');
                return;
            }
        }
    }

    public function getMinutesDuration()
    {
        $format = "Y-m-d H:i:s";
        $toDate = \DateTime::createFromFormat($format, $this->toDate) ?: $this->dateTimeFromIncomingFormat($this->toDate);
        $fromDate = \DateTime::createFromFormat($format, $this->fromDate) ?: $this->dateTimeFromIncomingFormat($this->fromDate);
        return ($toDate->getTimestamp() - $fromDate->getTimestamp()) / 60;
    }


    public function checkSubject($attribute, $params)
    {
        $exists = SubjectHelper::getAccountSubjects(
            $this->tutor,
            '',
            $this->student->isPatient() ? $this->jobId : null,
            true
        )->andwhere([Subject::tableName() . '.id' => $this->$attribute])->exists();

        if (!$exists) {
            $this->addError($attribute, 'Select subject');
        }
    }

    public function previousMonthValidator($attribute, $params)
    {
        $previousMonthDate = new \DateTime();
        $previousMonthDate->modify('-1 months');
        $previousMonthDate->setDate(
            $previousMonthDate->format('Y'),
            $previousMonthDate->format('m'),
            1
        );

        if (strtotime($this->toDate) < $previousMonthDate->format('U')) {
            $this->addError($attribute, static::DATE_MIN_DATE_ERROR_MESSAGE);
        }
    }

    public function validateToDate($attribute, $params)
    {
        if (empty($this->toDate)) {
            return;
        }

        $now = DateTime::createFromFormat(
            self::INCOMING_DATE_FORMAT,
            Timezone::staticConvertToServerTimeZone('now')
        )->modify('+1 day')->getTimestamp();
        $toDate = strtotime($this->toDate);
        $resTime = $toDate - strtotime($this->fromDate);
        if ($resTime < 0 || $toDate > $now) {
            $this->addError($attribute, static::DATE_FROM_GREATER_THAN_DATE_TO_ERROR_MESSAGE);
        } elseif ($resTime == 0) {
            $this->addError($attribute, 'Lesson duration can not be 0 minutes.');
        }
    }

    public static function find()
    {
        return new LessonQuery(static::class);
    }

    public function validateLongLesson($attribute, $params)
    {
        /**
         * @var $lesson self
         */
        $lesson = static::find()
            ->andWhere(
                [
                    'and',
                    ['>', 'fromDate', $this->fromDate],
                    ['<', 'toDate', $this->toDate],
                ]
            )
            ->andWhere(
                [
                    /*change to "or" to disallow submit session with same client at same time (for different tutors)*/
                    'and',
                    ['=', 'studentId', $this->studentId],
                    ['=', 'tutorId', $this->tutorId],
                ]
            )
            ->andWhere(['not', [static::tableName() . '.id' => $this->id]])
            ->one();

        if ($lesson) {
            $this->addError(
                $attribute,
                strtr(
                    $lesson->tutorId == $this->tutorId ? $params['messageWithStudentName'] : $params['message'],
                    [
                        '{studentName}' => $lesson->student->getDisplayName(),
                    ]
                )
            );
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateAllowedTime($attribute, $params)
    {
        /**
         * @var $lesson self
         */
        $lesson = static::find()
            ->andWhere(
                [
                    'and',
                    ['<=', 'fromDate', $this->$attribute],
                    ['>=', 'toDate', $this->$attribute],
                ]
            )
            ->andWhere(
                [
                    /*change to "or" to disallow submit session with same client at same time (for different tutors)*/
                    'and',
                    ['=', 'studentId', $this->studentId],
                    ['=', 'tutorId', $this->tutorId],
                ]
            )
            ->andWhere(['not', [static::tableName() . '.id' => $this->id]])
            ->limit(1)
            ->one();

        if ($lesson) {
            $this->addError(
                $attribute,
                strtr(
                    $lesson->tutorId == $this->tutorId ? $params['messageWithStudentName'] : $params['message'],
                    [
                        '{studentName}' => $lesson->student->getDisplayName(),
                    ]
                )
            );
        }
    }

    public function getDifferenceTime()
    {
        return (strtotime($this->toDate) - strtotime($this->fromDate)) / 60;
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
     * @return string
     */
    public function getPaymentTransactionDate()
    {
        return $this->paymentDate ?? $this->createdAt;
    }

    /**
     * @return string
     */
    public function getToDateAs12Hours()
    {
        return Yii::$app->formatter->as12HoursDatetime($this->toDate);
    }

    /**
     * @return string
     */
    public function getFromDateAs12Hours()
    {
        return Yii::$app->formatter->as12HoursDatetime($this->fromDate);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'studentId' => 'Student',
            'tutorId' => 'Tutor',
            'fromDate' => 'From Date',
            'toDate' => 'To Date',
            'subjectId' => 'Subject',
            'hourlyRate' => 'Hourly Rate',
            'paymentDate' => 'Payment Date',
            'status' => 'Status',
            'amount' => 'Amount',
            'fee' => 'Fee',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(Transaction::class, ['objectId' => 'id'])->andOnCondition(['objectType' => Transaction::TYPE_LESSON]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubject()
    {
        return $this->hasOne(Subject::class, ['id' => 'subjectId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(AccountWithDeleted::class, ['id' => 'studentId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTutor()
    {
        return $this->hasOne(AccountWithDeleted::class, ['id' => 'tutorId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReview()
    {
        return $this->hasOne(Review::class, ['lessonId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJob()
    {
        return $this->hasOne(Job::class, ['id' => 'jobId']);
    }

    public function checkLeaveReview()
    {
        /**
         * 14 - 2 weeks is allowed leave review
         */
        return (time() - strtotime($this->createdAt)) / 86400  < 14;
    }

    public function getAmount($account = null)
    {
        /**
         * @var $tutor Account
         */
        if (!empty($account)) {
            $tutor = $account;
        } else {
            $tutor = Yii::$app->user->identity;
        }
        if ($this->student->isPatient()) {
            /**
             * @var $jobHire JobHire
             */
            $jobHire = $this->job->getJobHires()->andWhere(['tutorId' => $this->tutorId])->one();
            $fullRate = $jobHire->getFullAmount();
            $tutorRate = $jobHire->getTutorRate();
        } else {
            $fullRate = $tutor->rate->getFullRate($tutor);
            $tutorRate = $tutor->rate->hourlyRate;
        }

        $resTime = strtotime($this->toDate) - strtotime($this->fromDate);
        $hours = $resTime / (60 * 60);
        $totalAmount = round($hours * $fullRate, 2);
        $fee = round($totalAmount - ($tutorRate * $hours), 2);

        $amount = $totalAmount - $fee;

        return [
            'amount' => (double)$amount,
            'fee' => (double)$fee,
            'tutorRate' => (double)$tutorRate,
            'fullRate' => $fullRate
        ];
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        EventHelper::changeLessonEvent(
            $this,
            $insert,
            $changedAttributes
        );

        if ($insert) {
            $studentAccount = $this->student;
            if ($studentAccount->isPatient()) {
                $studentAccount->setActiveLessonsStatus();
            }

            if (!empty($this->jobId)) {
                $jobHireId = JobHire::find()
                    ->select('id')
                    ->andWhere(['tutorId' => $this->tutorId])
                    ->andWhere(['jobId' => $this->jobId])
                    ->scalar();
                if ($jobHireId) {
                    QueueHelper::recalculateTutoringHours($jobHireId);
                }

                $countLessons = static::find()
                    ->andWhere(['studentId' => $this->studentId])
                    ->andWhere(['tutorId' => $this->tutorId])
                    ->count();

                //if it was second lesson between current student and tutor change flag to Light Blue
                if ((int)$countLessons === 2 && $this->student->isPatient()) {
                    $client = $this->student;
                    $client->flag = AccountClient::FLAG_LIGHT_BLUE;
                    $client->save(false);
                }
            }
        }
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['subject'] = 'subject';
        return $fields;
    }

    public function getDuration()
    {
        $format = "Y-m-d H:i:s";
        $toDate = \DateTime::createFromFormat($format, $this->toDate) ?: $this->dateTimeFromIncomingFormat($this->toDate);
        $fromDate = \DateTime::createFromFormat($format, $this->fromDate) ?: $this->dateTimeFromIncomingFormat($this->fromDate);
        $diff = date_diff($toDate, $fromDate);
        $hours = $diff->h;
        $minutes = $diff->i;
        return ($hours ? $hours . \Yii::$app->formatter->pluralForm($hours, [' hour ', ' hours ', ' hours ']) : '') . ($minutes ? $minutes . \Yii::$app->formatter->pluralForm($minutes, [' minute', ' minutes', ' minutes']) : '');
    }

    protected function getStatusLabels()
    {
        if (empty($this->statusLabels)) {
            $isTutor = Yii::$app->user->identity->isTutor();
            $this->statusLabels = [
                Transaction::STATUS_NEW => 'Pending',
                Transaction::STATUS_PENDING => 'Pending',
                Transaction::STATUS_ERROR => 'Charge failed',
                Transaction::STATUS_WAITING_FOR_APPROVE => $isTutor ? 'Waiting for approve' : 'Pending',
                Transaction::STATUS_REJECTED => 'Rejected',
                Transaction::STATUS_SUCCESS => $isTutor ? 'Success' : 'Charged',
            ];
        }
        return $this->statusLabels;
    }

    protected function dateTimeFromIncomingFormat($dateString)
    {
        return \DateTime::createFromFormat(static::INTERNAL_DATE_FORMAT, $dateString);
    }

    public function getFormattedLessonDate($format = 'Y/m/d')
    {
        return \DateTime::createFromFormat('Y-m-d H:i:s', $this->createdAt)->format($format);
    }

    public function transactionComplete()
    {
        $this->paymentDate = date('Y-m-d H:i:s');
    }


    public function getFromDateHourAs12Format()
    {
        return Yii::$app->formatter->asDate($this->fromDate, 'php: g:i A');
    }

    public function getToDateHourAs12Format()
    {
        return Yii::$app->formatter->asDate($this->toDate, 'php: g:i A');
    }

    public function getConvertedFromDate()
    {
        return Timezone::staticConvertFromServerTimeZone($this->fromDate);
    }

    public function setConvertedFromDate($value)
    {
        $this->fromDate = $value;
    }

    public function getConvertedToDate()
    {
        return Timezone::staticConvertFromServerTimeZone($this->toDate);
    }

    public function setConvertedToDate($value)
    {
        $this->toDate = $value;
    }

    public function convertToIncomingFormat($dateString)
    {
        $dateTime = new \DateTime($dateString, new \DateTimeZone(\Yii::$app->formatter->timeZone));
        return $dateTime->format(static::INCOMING_DATE_FORMAT);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudentProfile()
    {
        return $this->hasOne(Profile::class, ['accountId' => 'studentId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTutorProfile()
    {
        return $this->hasOne(Profile::class, ['accountId' => 'tutorId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientBalanceTransaction()
    {
        return $this->hasOne(ClientBalanceTransaction::class, ['transactionId' => 'id'])->via('parentTransaction');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentTransaction()
    {
        return $this->hasOne(Transaction::class, ['objectId' => 'id'])
            ->andOnCondition(['objectType' => Transaction::lessonTypes()])
            ->andOnCondition(['parentId' => null]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions()
    {
        return $this->hasMany(Transaction::class, ['objectId' => 'id'])->andOnCondition(['objectType' => Transaction::TYPE_LESSON]);
    }

    public function calculateClientPrice()
    {
        $submittedMinutes = $this->getDifferenceTime();
        $submittedHours = $submittedMinutes / 60;
        return $submittedHours * $this->job->clientBillRate;
    }

    public function getClientPrice()
    {
        if (!$this->student->isPatient()) {
            return null;
        }
        return $this->calculatedClientPrice ?? $this->calculateClientPrice();
    }

    public function getStudentPrice()
    {
        if ($this->student->isPatient()) {
            $price = $this->calculateClientPrice();
        } else {
            $amount = $this->getAmount($this->tutor);
            $price = $amount['amount'] + $amount['fee'];
        }
        return $price;
    }

    public function getStudentHourlyRate()
    {

        if ($this->student->isPatient()) {
            $rate = $this->job->clientBillRate;
        } else {
            $amount = $this->getAmount($this->tutor);
            $rate = $amount['fullRate'];
        }
        return $rate;
    }


    public function getLastTransaction()
    {
        $transactionClass = Yii::$app->getModulePayment()->modelStatic('Transaction');
        return $this->hasOne($transactionClass, ['objectId' => 'id'])
            ->andWhere([
                'objectType' => [Transaction::TYPE_LESSON, Transaction::TYPE_LESSON_BATCH_PAYMENT],
            ])
            ->andWhere(
                [
                    'in',
                    'type',
                    [
                        Transaction::STRIPE_CAPTURE,
                        Transaction::STRIPE_CHARGE,
                        Transaction::STRIPE_TRANSFER,
                        Transaction::STRIPE_REFUND
                    ]
                ]
            )
            ->orderBy($transactionClass::tableName() . '.createdAt DESC');
    }

    public function getLastCharge()
    {
        $transactionClass = Yii::$app->getModulePayment()->modelStatic('Transaction');
        return $this->hasOne($transactionClass, ['objectId' => 'id'])
            ->andWhere([
                'objectType' => Transaction::lessonTypes(),
            ])
            ->andWhere(['in', 'type', [Transaction::STRIPE_CHARGE]])
            ->orderBy($transactionClass::tableName() . '.createdAt DESC');
    }

    public function getStatusString()
    {
        $transaction = $this->lastTransaction;
        if (!$transaction) {
            return 'Pending';
        }

        if ($transaction->isTypeRefund() && $transaction->isStatusSuccess()) {
            //for tutor display refund success after revers transfer complete
            //for company client (lesson batch payments) display Refund Pending until funds not returned to client
            //(client balance, transaction related to partial refund of group transaction, not created )
            $clientBalanceRelatedToPartialRefund = Transaction::find()
                ->andWhere(['objectType' => Transaction::TYPE_LESSON])
                ->andWhere([Transaction::tableName() . '.type' => Transaction::PARTIAL_REFUND])
                ->andWhere(['objectId' => $this->id])
                ->joinWith('relatedClientBalance')
                ->andWhere(['not', [ClientBalanceTransaction::tableName() . '.id' => null]]);

            if (
                Yii::$app->user->isPatient()
                && $transaction->isLessonBatchPayment()
                && !$clientBalanceRelatedToPartialRefund->exists()
            ) {
                return 'Refund Pending';
            }

            return 'Refunded';
        } elseif ($transaction->isTypeRefund() && $transaction->isStatusError()) {
            return 'Refund failed';
        }
        return $this->getStatusLabels()[$transaction->status];
    }

    public function getPayoutDate()
    {
        $transaction = $this->lastTransaction;
        if (
            $transaction
            && (
                $transaction->isStatusRejected()
                || $transaction->isTypeRefund()
                || $transaction->isTypeError()
            )
        ) {
            return null;
        }

        return Transaction::calcExpectedPayoutDate(
            $this->student,
            $transaction && $transaction->processDate ?
                DateTime::createFromFormat('Y-m-d', $transaction->processDate) :
                DateTime::createFromFormat('Y-m-d H:i:s', $this->createdAt)
        );
    }

    /**
     * @param $userId
     * @return LessonQuery
     */
    public static function findStudentLessons($userId)
    {
        return static::find()->ofStudent($userId);
    }

    /**
     * change lesson data after refund of transaction
     * @param bool $runValidation
     */
    public function saveRefundedStatus($runValidation = false)
    {
        $this->paymentDate = date('Y-m-d');
        $this->save($runValidation);
    }
}

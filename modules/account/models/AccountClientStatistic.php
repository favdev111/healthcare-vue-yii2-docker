<?php

namespace modules\account\models;

use common\models\Coefficients;
use modules\account\helpers\ConstantsHelper;
use modules\account\models\api\ClientRefund;
use modules\account\models\api\ClientRematch;
use modules\account\Module;
use Yii;
use yii\base\InvalidCallException;
use yii\db\Expression;

/**
 * This is the model class for table "{{%account_client_statistic}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property string $lastLessonDate
 * @property integer $clientLessonStatus
 * @property integer $clientPaymentStatus
 * @property integer $jobPostingsCount
 * @property double $balance
 * @property string $updatedAt
 * @property string $lastVisit
 * @property integer $countMessages
 * @property integer $lastMessageDate
 * @property string $formattedBalance
 * @property int $congratulationEmailSent
 * @property int $countAccountInactiveEmails
 * @property bool $inactiveEmailAnswer
 * @property double $totalEarned
 * @property double $hoursPerRelation
 * @property-read double $rematchesPerMatchPercent
 * @property-read double $refundsPerMatchPercent
 *
 * @property Account $account
 */
class AccountClientStatistic extends \yii\db\ActiveRecord
{

    public const LAST_LOGIN_FORMAT = 'Y-m-d H:i:s';

    public const COUNTER_OF_MESSAGE_FROM_PROFILE_FORM = 'countMessages';

    public const LIMIT_FOR_MESSAGES_FROM_PROFILE_FORM = 10;

    public const INACTIVE_ACCOUNT_MODE_ACTIVE = 'active';
    public const INACTIVE_ACCOUNT_MODE_INACTIVE = 'inactive';

    public static $inactiveEmailModes = [
        self::INACTIVE_ACCOUNT_MODE_ACTIVE,
        self::INACTIVE_ACCOUNT_MODE_INACTIVE,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_statistic}}';
    }

    public function rules()
    {
        return [
            [['lastLessonDate'], 'default', 'value' => null],
            [['clientLessonStatus'], 'default', 'value' => ConstantsHelper::LESSON_STATUS__REQUIRES_TUTOR],
            [['clientPaymentStatus'], 'default', 'value' => ConstantsHelper::PAYMENT_STATUS__NO_PAYMENT_ADDED],
            [['jobPostingsCount'], 'default', 'value' => 0],
            [['balance'], 'default', 'value' => 0],
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
                'createdAtAttribute' => null,
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'accountId']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        /**
         * @var $accountModule Module
         */
        $accountModule = Yii::$app->getModule('account');

        if (isset($changedAttributes['hoursPerRelation'])) {
            $accountModule->updateTutorSearchIndex($this->accountId, ['hoursPerRelation' => $this->hoursPerRelation]);
        }
    }

    /**
     * @param null $id
     * @return array|bool|AccountClientStatistic|null|\yii\db\ActiveRecord
     */
    public static function getUserStatistic($id = null)
    {
        if (!$id && !Yii::$app->user->id) {
            return false;
        } elseif (!$id) {
            $id = Yii::$app->user->id;
        }
        $model = static::find()->andWhere(['accountId' => $id])->limit(1)->one();
        if (empty($model)) {
            $model = new AccountClientStatistic();
            $model->accountId = $id;
            // Fill default values
            $model->validate();
        }

        return $model;
    }

    public function isAllowedToSendMessagesInProfileForm()
    {
        $notMoreThanLimit =  ($this->countMessages < static::LIMIT_FOR_MESSAGES_FROM_PROFILE_FORM);

        $lastMessageTimestamp = \DateTime::createFromFormat(Yii::$app->formatter->MYSQL_DATETIME, $this->lastMessageDate);
        if ($lastMessageTimestamp) {
            $lastMessageTimestamp = $lastMessageTimestamp->getTimestamp();
            $dateCheck =  (time() >= $lastMessageTimestamp + (60 * 60 * 24));
        }
        $dateCheck = $dateCheck ?? true;

        // set counter to 0 after when block time end
        if (!$notMoreThanLimit && $dateCheck) {
            $this->countMessages = 0;
            $this->save();
        }
        if ($notMoreThanLimit) {
            return true;
        } elseif (!$notMoreThanLimit && $dateCheck) {
            return true;
        }
        return false;
    }

    public function getLastVisitTimestamp()
    {
        $lastVisit = \DateTime::createFromFormat(Yii::$app->formatter->MYSQL_DATETIME, $this->lastVisit ?? false);
        if ($lastVisit) {
            $lastVisit = $lastVisit->getTimestamp();
        }
        return $lastVisit ?: 0;
    }

    /**
     * @return string
     */
    public function getFormattedBalance()
    {
        return ($this->balance || $this->balance == 0) ? number_format($this->balance, 2) : '';
    }

    public function calculateHoursPerRelation(): float
    {
        if (!$this->account->isTutor()) {
            throw new InvalidCallException('Method can only be called with tutors accounts.');
        }
        $inSeconds = (int)(JobHire::find()->activeOrWasActive()->byTutor($this->accountId)->average('tutoringHours'));
        return $inSeconds / 60 / 60;
    }

    public function calculateLastVisitPoints()
    {
        $lastVisitTimeStamp = strtotime($this->lastVisit) ?? 0;
        /*diff in seconds*/
        $diff = time() - $lastVisitTimeStamp;
        if ($diff <= 0) {
            return 0;
        }
        /*diff in hours*/
        $hours = round($diff / 3600);

        $coefficient = TutorScoreSettings::getLastLoginCoefficient($hours);
        return $coefficient;
    }

    public function calculateHoursPerRelationPoints()
    {
        return TutorScoreSettings::getHoursPerRelationScorePoints(
            $this->hoursPerRelation ?? 0
        );
    }

    public function getRematchesPerMatchPercent(): float
    {
        $rematchesIds = RematchJobHire::find()
            ->joinWith('jobHire')
            ->joinWith('accountReturn')
            ->andWhere(['tutorId' => $this->accountId])
            ->andWhere(
                [
                    'reasonCode' => [
                        ClientRematch::REASON_UNSATISFIED,
                        ClientRematch::REASON_CAN_NOT_CONNECT,
                        ClientRematch::REASON_TUTOR_DOES_NOT_WANT_TO_CONTINUE,
                        ClientRematch::REASON_TUTOR_UNAVAILABLE,
                    ],
                ]
            )
            ->select(RematchJobHire::tableName() . '.id')
            ->column();

        $rematchCount = RematchJobHire::find()
            ->select('jobHireId')
            ->andWhere(['id' => $rematchesIds])
            ->groupBy('jobHireId')
            ->count();


        $jobHireCount = (float)JobHire::find()
            ->activeOrWasActive()
            ->byTutor($this->accountId)
            ->count();

        if (!(bool)$jobHireCount) {
            return 0;
        }

        return $rematchCount / $jobHireCount * 100;
    }

    public function getRefundsPerMatchPercent(): float
    {
        $jobHireQuery = JobHire::find()
            ->activeOrWasActive()
            ->byTutor($this->accountId);

        $allJobHiresIds = (clone $jobHireQuery)->select('id')->column();
        $jobHireCount = count($allJobHiresIds);

        if (!(bool)$jobHireCount) {
            return 0;
        }

        $refundedHiresIds = (clone AccountReturn::find())
            ->refunds()
            ->andWhere([
                'reasonCode' => [
                    ClientRefund::REASON_UNSATISFIED,
                    ClientRefund::REASON_ATTENDANCE_ISSUE,
                    ClientRefund::REASON_TUTOR_REFUSE
                ],
            ])
            ->byJobHireId($jobHireQuery->select('id'))
            ->select('jobHireId')
            ->groupBy('jobHireId')
            ->column();

        $refundCount = count(array_intersect($allJobHiresIds, $refundedHiresIds));

        return $refundCount / $jobHireCount * 100;
    }

    public function calculateRematchesPerMatchPoints()
    {
        return TutorScoreSettings::getRematchPerMatchScorePoints(
            $this->rematchesPerMatchPercent ?? 0
        );
    }

    public function calculateRefundsPerMatchPoints()
    {
        return TutorScoreSettings::getRefundPerMatchScorePoints(
            $this->refundsPerMatchPercent ?? 0
        );
    }

    public function calculateHoursPerSubjectPoints(array $subjectIds)
    {
        $countHours = Lesson::find()
            ->select(new Expression('ROUND(SUM(TIME_TO_SEC(TIMEDIFF(toDate, fromDate))/3600)) as totalHours'))
            ->ofTutor($this->accountId)
            ->bySubject($subjectIds)
            ->scalar();

        return TutorScoreSettings::getHoursPerSubjectScorePoints($countHours);
    }
}

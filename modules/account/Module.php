<?php

namespace modules\account;

use common\components\Module as BaseModule;
use common\events\NotificationEvent;
use common\helpers\QueueHelper;
use common\helpers\Role;
use common\helpers\Url;
use common\models\Review;
use common\models\Zipcode;
use common\components\ZipCodeHelper;
use modules\account\models\Account;
use modules\account\models\api\JobHire;
use modules\account\models\api\Tutor;
use modules\account\models\Job;
use modules\account\models\JobApply;
use modules\account\models\JobOffer;
use modules\account\models\ListAddedAccount;
use modules\account\models\TutorScoreSettings;
use modules\account\models\TutorSearch;
use modules\chat\events\StatusChangeEvent;
use modules\chat\models\Chat;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\base\InvalidCallException;
use yii\elasticsearch\Exception;
use yii\helpers\ArrayHelper;
use yii\base\Application;

class Module extends BaseModule implements BootstrapInterface
{
    const EVENT_ACCOUNT_CHANGE = 'accountChangeEvent';
    const EVENT_PROFILE_CHANGE = 'profileChangeEvent';
    const EVENT_LESSON_CHANGE = 'lessonChangeEvent';
    const EVENT_REVIEW_CHANGE = 'reviewChangeEvent';
    const EVENT_REVIEW_DELETED = 'reviewDeletedEvent';
    const EVENT_RATING_CHANGE = 'ratingChangeEvent';
    const EVENT_HOURLY_RATE_CHANGE = 'hourlyRateChangeEvent';
    const EVENT_SUBJECT_CHANGE = 'subjectChangeEvent';
    const EVENT_AVATAR_CHANGE = 'avatarChangeEvent';
    const EVENT_APPLIED_JOB = 'appliedJob';
    const EVENT_LEAVED_REVIEW = 'leavedReview';
    const EVENT_STUDENT_REPLIED_JOB_APPLICATION = 'studentRepliedToJob';

    /**
     * TODO: Deprecated
     */
    const EVENT_NEW_JOB_POSTED = 'newJobPosted';

    const EVENT_NEW_JOB = 'newJob';
    const EVENT_JOB_CLOSED = 'jobClosed';
    const EVENT_JOB_OPENED = 'jobOpened';
    const EVENT_MISSING_TUTOR_INFORMATION = 'missingInformation';
    const EVENT_NEW_MESSAGE_TUTOR = 'newMessageTutor';
    const EVENT_NEW_MESSAGE_STUDENT = 'newMessageStudent';
    const EVENT_TUTOR_HIRED = 'tutorHired';
    const EVENT_OFFER_CONFIRMED = 'offerConfirmed';
    const EVENT_OFFER_DECLINED = 'offerDeclined';
    const EVENT_NEW_OFFER = 'newOffer';
    const EVENT_HIRE_DECLINED_BY_COMPANY = 'hireDeclinedByCompany';
    const EVENT_NEW_CLIENT_BALANCE_TRANSACTION = 'newClientBalanceTransaction';
    const EVENT_CLIENT_NEGATIVE_BALANCE = 'clientNegativeBalance';

    const TUTOR_DASHBOARD_ROUTE = 'account/dashboard-tutor/index';

    const DASHBOARD_ROLES = [
        Role::ROLE_SPECIALIST => self::TUTOR_DASHBOARD_ROUTE,
    ];

    /**
     * @var string $onlineTutoringApiUrl - use for API request in Online tutoring
     */
    public $onlineTutoringApiUrl;

    /**
     * @var string $onlineTutoringApiKey - api key that use for API request in Online Tutoring
     */
    public $onlineTutoringApiKey;

    /**
     * @var string Alias for module
     */
    public $alias = "@account";

    /**
     * @var int Login duration
     */
    public $loginDuration = 2592000; // 1 month

    /**
     * @var array|string|null Url to redirect to after logging in. If null, will redirect to home page. Note that
     *                        AccessControl takes precedence over this (see [[yii\web\User::loginRequired()]])
     */
    public $loginRedirect = '/dashboard';

    /**
     * @var array|string|null Url to redirect to after logging out. If null, will redirect to home page
     */
    protected $logoutRedirect = null;

    /**
     * @var bool If true, users will have to confirm their email address after registering (= email activation)
     */
    public $emailConfirmation = true;

    /**
     * @var bool If true, users will have to confirm their email address after changing it on the account page
     */
    public $emailChangeConfirmation = true;

    /**
     * @var string Reset password token expiration (passed to strtotime())
     */
    public $resetExpireTime = "2 days";

    /**
     * @var string Login via email token expiration (passed to strtotime())
     */
    public $loginExpireTimeRemmember = "30 days";

    public $loginExpireTime = "1 day";

    public $activeJobTime = "-28 days";

    public $pathToAvatar;
    public $pathToSignatures;
    public $pathToSignedPdf;
    public $pathToTutorAgreementPdf;
    public $urlToAvatar;

    public $hourlyRateMax = 300;
    public $hourlyRateMin = 20;
    public $hourlyRateMaxShowOnSearch = 125;
    public $hourlyRateMinShowOnSearch = 25;
    public $ageMin = 18;
    public $ageMax = 80;

    public function init()
    {
        parent::init();

        if (!$this->pathToAvatar) {
            $this->pathToAvatar = \Yii::getAlias('@uploads') . '/avatar/';
        }
        if (!$this->pathToSignatures) {
            $this->pathToSignatures = Yii::getAlias('@uploads') . '/signatures/';
        }
        if (!$this->pathToSignedPdf) {
            $this->pathToSignedPdf = Yii::getAlias('@uploads') . '/signaturePdf/';
        }

        if (!$this->pathToTutorAgreementPdf) {
            $this->pathToTutorAgreementPdf = Yii::getAlias('@uploads') . '/specialistAgreementPdf/';
        }
        if (!$this->urlToAvatar) {
            $this->urlToAvatar = '/uploads/avatar/';
        }
    }

    /**
     * Get default model classes
     */
    protected function getDefaultModelClasses()
    {
        return [
            'Account' => 'modules\account\models\Account',
            'AccountWithoutRestrictions' => 'modules\account\models\AccountWithDeleted',
            'AccountClient' => 'modules\account\models\Account',
            'AccountAccessToken' => 'modules\account\models\AccountAccessToken',
            'AccountScore'               => 'modules\account\models\AccountScore',
            'Profile' => 'modules\account\models\Profile',
            'AccountSubject' => 'modules\account\models\AccountSubject',
            'AccountEducation' => 'modules\account\models\Education',
            'AccountReward' => 'modules\account\models\Reward',
            'AccountRate' => 'modules\account\models\Rate',
            'Role' => 'modules\account\models\Role',
            'Token' => 'modules\account\models\Token',
            'Auth' => 'modules\account\models\Auth',
            'LoginForm' => 'modules\account\models\forms\LoginForm',
            'ClientLoginForm' => 'modules\account\models\forms\ClientLoginForm',
            'UserSearch' => 'modules\account\models\search\UserSearch',
            'LoginEmailForm' => 'modules\account\models\forms\LoginEmailForm',
            'ProfileClientForm' => 'modules\account\models\forms\ProfileClientForm',
            'Job' => 'modules\account\models\Job',
            'JobSearch' => 'modules\account\models\search\JobSearch',
            'JobApply' => 'modules\account\models\JobApply',
            'JobHire' => 'modules\account\models\JobHire',
            'JobOffer' => 'modules\account\models\JobOffer',
        ];
    }

    /**
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        foreach ($this->triggerEvents() as $class) {
            $class::init();
        }

        Event::on(
            \yii\base\Module::class,
            self::EVENT_AVATAR_CHANGE,
            function ($event) {
                $account = $event->model;
                $account->touch('updatedAt');
            }
        );


        Event::on(
            \yii\base\Module::class,
            \modules\chat\Module::EVENT_CHAT_ACCOUNT_SUSPICIOUS,
            function (StatusChangeEvent $event) {
                // Suspending student jobs (hide from public)
                $account = Account::findOne($event->accountId);
                if ($account) {
                    $route = 'job/suspend-student-jobs';
                    $data = ['accountId' => $account->id];
                    $task = new \UrbanIndo\Yii2\Queue\Job(['route' => $route, 'data' => $data]);
                    Yii::$app->queue->post($task);
                }
            }
        );

        Event::on(
            \yii\base\Module::class,
            \modules\chat\Module::EVENT_CHAT_ACCOUNT_UNBLOCKED,
            function (StatusChangeEvent $event) {
                // UnSuspending student jobs (show back to public)
                $account = Account::findOne($event->accountId);
                if ($account) {
                    $route = 'job/un-suspend-student-jobs';
                    $data = ['accountId' => $account->id];
                    $task = new \UrbanIndo\Yii2\Queue\Job(['route' => $route, 'data' => $data]);
                    Yii::$app->queue->post($task);
                }
            }
        );

        Event::on(
            \yii\base\Module::class,
            self::EVENT_TUTOR_HIRED,
            function (NotificationEvent $event) {
                /**
                 * @var Account $tutor ;
                 */
                $tutor = $event->tutor;

                /**
                 * @var Job $job
                 */
                $job = $event->job;

                if (!$tutor->getListAddedAccount()->andWhere(['accountId' => $job->accountId])->exists()) {
                    // No need to proceed in case such relation already exists.
                    $addAccount = new ListAddedAccount([
                        'ownerId' => $tutor->id,
                        'accountId' => $job->accountId,
                    ]);
                    if (!$addAccount->save()) {
                        Yii::error('Failed to add student to tutors list during Hire. Errors: ' .
                            json_encode($addAccount->getErrors()), 'b2b');
                    }
                }

                //only for b2b jobs - notify not applied tutors
                if ($job->isB2bJob()) {
                    QueueHelper::notifyApplicants($job->id, $tutor->id, Yii::$app->user->id);
                }
            }
        );

        Event::on(
            \yii\base\Module::class,
            self::EVENT_OFFER_CONFIRMED,
            function (NotificationEvent $event) {
                /**
                 * @var $job Job
                 */
                $job = $event->job;
                /**
                 * @var $tutor Tutor
                 */
                $tutor = $event->tutor;

                $jobHire = JobHire::find()->andWhere(['jobId' => $job->id])->andWhere(['tutorId' => $tutor->id])->one();
                if (!$jobHire) {
                    /**
                     * @var $accountModule Module
                     */
                    $accountModule = Yii::$app->getModule('account');
                    $jobHire = $accountModule->model('JobHire', [
                        'jobId' => $job->id,
                        'tutorId' => $tutor->id,
                        'status' => JobHire::STATUS_HIRED,
                        'price' => $job->latestJobOffer->amount,
                        'shareContactInfo' => $event->shareContactInfo,
                    ]);
                }

                if (!$jobHire->save()) {
                    Yii::error('Failed to save job hire on offer confirm', 'b2b');
                }
            }
        );

        Event::on(
            \yii\base\Module::class,
            self::EVENT_OFFER_DECLINED,
            function (NotificationEvent $event) {
                /**
                 * @var $job Job
                 */
                $job = $event->job;
                /**
                 * @var $tutor Tutor
                 */
                $tutor = $event->tutor;

                $jobHire = JobHire::find()->andWhere(['jobId' => $job->id])->andWhere(['tutorId' => $tutor->id])->one();
                if (!$jobHire) {
                    /**
                     * @var $accountModule Module
                     */
                    $accountModule = Yii::$app->getModule('account');
                    $jobHire = $accountModule->model('JobHire', [
                        'jobId' => $job->id,
                        'tutorId' => $tutor->id,
                        'status' => $job->latestJobOffer->type === JobOffer::TYPE_OFFERED_BY_TUTOR
                            ? JobHire::STATUS_DECLINED_BY_COMPANY
                            : JobHire::STATUS_DECLINED_BY_TUTOR,
                    ]);
                }

                if (!$jobHire->save()) {
                    Yii::error('Failed to save job hire on offer decline', 'b2b');
                }
            }
        );

        Event::on(static::class, static::EVENT_NEW_OFFER, function ($event) {
            /**
             * @var $job \modules\account\models\Job
             */
            $job = $event->job;
            /**
             * @var $tutor Account
             */
            $tutor = $event->tutor;
            /**
             * @var $jobOffer JobOffer
             */
            $jobOffer = $event->jobOffer;

            if ($jobOffer->type === JobOffer::TYPE_OFFERED_BY_COMPANY) {
                Yii::$app->pushMessage->newJobOfferFromCompany(
                    $job,
                    $tutor
                );
            }
        });

        parent::bootstrap($app);
    }

    public function getLogoutRedirect()
    {
        return Url::toRoute('/login');
    }

    public function getAvatarPath($size = null, $account = null)
    {
        if (is_null($account)) {
            if (Yii::$app->user->isGuest) {
                return false;
            }
            $account = Yii::$app->user->identity;
        }

        $suffix = '.png';
        if (!is_null($size)) {
            $suffix = '_' . $size . '.jpg';
        }

        return $this->pathToAvatar . $account->publicId . $suffix;
    }

    /**
     * @param $roleId
     * @param $normalized
     * @return mixed
     */
    public static function getDashboardRoute($roleId, $normalized = false)
    {
        if (!isset(static::DASHBOARD_ROLES[$roleId])) {
            throw new \InvalidArgumentException("Dashboard route for - \"$roleId\" is not found");
        }

        $route = static::DASHBOARD_ROLES[$roleId];

        if ($normalized) {
            $route = '/' . $route;
        }

        return $route;
    }

    /**
     * @param boolean $normalized
     * @return mixed
     */
    public static function getIdentityDashboardRoute($normalized = false)
    {
        /**
         * @var Account $identity
         */
        $identity = Yii::$app->user->identity;
        if (!$identity) {
            throw new InvalidCallException('Method requires identity user');
        }

        return static::getDashboardRoute($identity->roleId, $normalized);
    }

    public static $urlRules = [
        Role::ROLE_SPECIALIST => [
            'profile' => 'account/profile-tutor/about-me',
            'profile/remove-bank-account/<id:\d+>' => 'account/profile-tutor/remove-bank-account',
            'profile/active-bank-account/<id:\d+>' => 'account/profile-tutor/active-bank-account',
            'profile/set-hourly-rate' => 'account/profile-tutor/set-hourly-rate',
            'profile/set-avatar' => 'account/profile-tutor/set-avatar',
            'profile/subjects' => 'account/profile-tutor/subjects',
            'profile/edit-profile' => 'account/profile-tutor/edit-profile',
            'profile/save-availability' => 'account/profile-tutor/save-availability',
            'profile/notification' => 'account/profile-tutor/notification',
            'profile/payment-info' => 'account/profile-tutor/payment-info',
            'profile/payment-extra-info' => 'account/profile-tutor/stripe-extra-data',
            'profile/payment-extra-info-document' => 'account/profile-tutor/stripe-extra-data-document',
            'profile/password' => 'account/profile-tutor/password',
            'dashboard' => self::TUTOR_DASHBOARD_ROUTE,
            'public-profile' => 'account/profile-tutor/public-profile',
            'show-profile' => 'account/profile-tutor/show-market-place',
            'hide-profile' => 'account/profile-tutor/hide-market-place',
            'lessons' => 'account/lessons/index-tutor',
            'terms-pdf'                    => 'account/profile-tutor/terms-pdf',

            // Mobile
            'profile/personal-info' => 'account/profile-tutor/personal-info',
        ],
    ];

    /**
     * @param Application $app
     * @param array $rules
     */
    protected function addRules(Application $app, array $rules)
    {
        /**
         * Add account rules
         */
        $user = $app->user;

        $addRules = [];
        if (!$user->isGuest && isset($rules[$user->identity->roleId])) {
            $addRules = $rules[$user->identity->roleId];
        } else {
            foreach ($rules as $roleRules) {
                $addRules = ArrayHelper::merge($addRules, $roleRules);
            }
        }

        $app->getUrlManager()->addRules($addRules, false);
    }

    public function getScores($type)
    {
        $tutorScoreTypes = TutorScoreSettings::TYPES;
        if (!in_array($type, $tutorScoreTypes)) {
            Yii::error('Get Scores - Type is not supported.');
            throw new \Exception('Type is not supported');
        }

        $scores = TutorScoreSettings::findAll(['type' => $type]);
        return $scores;
    }

    public function updateTutorSearchIndex($accountId, array $attributes = [])
    {
        return true;
        /**
         * @var $accountModel Account
         */
        $accountModel = Account::findWithoutRestrictions()
            ->andWhere(['id' => $accountId])
            ->tutor()
            ->one();

        if (!$accountModel) {
            return false;
        }

        // @todo Check this, return 503
        try {
            $model = TutorSearch::findOne($accountId);
        } catch (Exception $exception) {
            $model = null;
        }

        if (!$model) {
            $model = new TutorSearch();
            $model->primaryKey = $accountId;
            $attributes = [];
        }

        if (empty($attributes)) {
            $profile = $accountModel->profile;
            $model->accountId = $accountModel->id;
            $model->rating = $accountModel->rating->totalRating ?? 0;
            $model->gender = $profile->gender;
            $model->zipCode = $profile->zipCode;
            $model->dateOfBirth = $profile->dateOfBirth;
            $model->cityId = $profile->city->id ?? null;
            $model->stateName = $profile->city->stateNameShort ?? null;
            // Not save as boolean
            $model->hideProfile = (int)$accountModel->hideProfile;
            $model->location = [
                'lat' => (float)$profile->latitude,
                'lon' => (float)$profile->longitude,
            ];
            $model->createdAt = Yii::$app->formatter->asTimestamp($accountModel->createdAt);

            $rate = $accountModel->rate;
            $model->hourlyRate = (float)(!empty($rate) ? $rate->getFullRate($accountModel) : null);
            $model->clearHourlyRate = (float)($rate->hourlyRate ?? null);
            $model->subjects = array_keys(
                $accountModel->getSubjects()->select(['subjectId'])->indexBy('subjectId')->asArray()->all()
            );
            $model->status = $accountModel->status;
            $model->blockReason = $accountModel->blockReason;

            $lastVisit = empty($accountModel->clientStatistic) ? 0 : $accountModel->clientStatistic->getLastVisitTimestamp();
            $model->lastVisit = $lastVisit;
            $model->availability = $accountModel->availability->value ?? 0;
            $model->receiveNewJobPostedNotifications = (int)$accountModel->isCanGetNewJobPostedNotifications();
            $model->countReviews = (int)$accountModel->getCountReview();
            $model->fullName = $accountModel->profile->fullName();
            $model->address = $accountModel->profile->address;
            $model->searchHide = $accountModel->searchHide;

            $model->hoursScore = $accountModel->getTeachHoursScore();
            $model->ratingScore = $accountModel->getRatingScore();
            $model->responseTimeScore = $accountModel->getResponseTimeScore();
            $model->contentScore = $accountModel->getContentScore();
            $model->hours = $accountModel->getTotalTeachHours();
            $model->responseTime = $accountModel->getAvgResponseTime();
            $model->hoursPerRelation = $accountModel->clientStatistic->hoursPerRelation ?? 0;
            $model->totalScore =
                $model->hoursScore
                + $model->ratingScore
                + ($model->responseTimeScore === null ? 0 : $model->responseTimeScore)
                + $model->contentScore;
        } else {
            foreach ($model->attributes() as $attribute) {
                if (isset($attributes[$attribute])) {
                    $model->$attribute = $attributes[$attribute];
                }
            }
        }

        return $model->save(false);
    }

    public function removeFromTutorSearchIndex($accountId)
    {
        return true;
        $accountModel = Account::findOneWithoutRestrictions($accountId);
        if (
            !$accountModel
            || ($accountModel->roleId != Role::ROLE_SPECIALIST)
        ) {
            return false;
        }

        // @todo Check this, return 503
        try {
            $model = TutorSearch::findOne($accountId);
            if ($model) {
                return $model->delete();
            }
        } catch (Exception $exception) {
        }

        return false;
    }


    public static function getAccountQueryByIp($ip)
    {
        return Account::find()
            ->joinWith('chat')
            ->andWhere([Account::tableName() . '.createdIp' => $ip]);
    }

    public static function isIpSuspicious($ip)
    {
        return self::getAccountQueryByIp($ip)->exists();
    }

    /**
     * @param Account $account
     * @return bool
     */
    public function verifyIpAddress(Account $account)
    {
        $accountQuery = self::getAccountQueryByIp($account->createdIp);
        /**
         * Check is account on hold
         */
        $isIpOnHold = (clone $accountQuery)
            ->andWhere([Chat::tableName() . '.status' => Chat::STATUS_HOLD])
            ->exists();

        if ($isIpOnHold) {
            $account->chat->status = Chat::STATUS_HOLD;
            $account->chat->statusReason = Chat::STATUS_REASON_IP_BLOCK;
            if (!$account->chat->save()) {
                Yii::error('Failed to update chat account status. Errors: ' . json_encode($account->chat->getErrors()), 'chat');
                return false;
            }
            return true;
        }

        /**
         * Check is account suspicious
         */
        $isIpSuspicious = $accountQuery
            ->andWhere([Chat::tableName() . '.status' => Chat::STATUS_SUSPICIOUS])
            ->exists();

        if ($isIpSuspicious) {
            $account->chat->status = Chat::STATUS_SUSPICIOUS;
            $account->chat->statusReason = Chat::STATUS_REASON_IP_BLOCK;
            if (!$account->chat->save()) {
                Yii::error('Failed to update chat account status. Errors: ' . json_encode($account->chat->getErrors()), 'chat');
                return false;
            }
        }
        return true;
    }

    public function eventTutorHired(\modules\account\models\JobHire $jobHire)
    {
        $event = new NotificationEvent();
        $event->tutor = $jobHire->tutor;
        $event->job = $jobHire->job;
        $event->shareContactInfo = $jobHire->shareContactInfo;
        $event->jobHire = $jobHire;
        Event::trigger(self::class, self::EVENT_TUTOR_HIRED, $event);
    }

    public function eventOfferConfirmed(JobOffer $jobOffer)
    {
        $event = new NotificationEvent();
        $event->jobOffer = $jobOffer;
        $event->tutor = $jobOffer->tutor;
        $event->job = $jobOffer->job;
        $event->shareContactInfo = $jobOffer->shareContactInfo;
        Event::trigger(self::class, self::EVENT_OFFER_CONFIRMED, $event);
    }

    public function eventOfferDeclined(JobOffer $jobOffer)
    {
        $event = new NotificationEvent();
        $event->tutor = $jobOffer->tutor;
        $event->job = $jobOffer->job;
        Event::trigger(self::class, self::EVENT_OFFER_DECLINED, $event);
    }

    public function eventNewOffer(JobOffer $jobOffer)
    {
        $event = new NotificationEvent();
        $event->tutor = $jobOffer->tutor;
        $event->job = $jobOffer->job;
        $event->jobOffer = $jobOffer;
        Event::trigger(self::class, self::EVENT_NEW_OFFER, $event);
    }

    public function eventHireDeclinedByCompany(\modules\account\models\JobHire $jobHire)
    {
        $event = new NotificationEvent();
        $event->jobHire = $jobHire;
        Event::trigger(self::class, self::EVENT_HIRE_DECLINED_BY_COMPANY, $event);
    }

    public function eventAppliedJob(JobApply $jobApply)
    {
        $event = new NotificationEvent();
        $event->jobApply = $jobApply;
        Event::trigger(self::class, self::EVENT_APPLIED_JOB, $event);
    }

    public function eventLeavedReview(Review $review)
    {
        $event = new NotificationEvent();
        $event->review = $review;
        Event::trigger(self::class, self::EVENT_LEAVED_REVIEW, $event);
    }

    /**
     * @param Job $job
     * @param $tutor
     * @param $message
     * @param int $chatUserId
     */
    public function eventStudentRepliedToJob(Job $job, $tutor, $message, int $chatUserId)
    {
        $event = new NotificationEvent();
        $event->job = $job;
        $event->account = $tutor;
        $event->message = $message;
        Event::trigger(self::class, self::EVENT_STUDENT_REPLIED_JOB_APPLICATION, $event);
    }

    public function eventNewJobPosted(Job $job, $owner, $checkSettings = true)
    {
        $event = new NotificationEvent();
        $event->job = $job;
        $event->owner = $owner;
        $event->checkSettings = $checkSettings;
        Event::trigger(self::className(), self::EVENT_NEW_JOB_POSTED, $event);
    }

    public function eventNewJob(Job $job)
    {
        $event = new NotificationEvent();
        $event->job = $job;
        Event::trigger(self::className(), self::EVENT_NEW_JOB, $event);
    }

    public function eventJobClosed(Job $job)
    {
        $event = new NotificationEvent();
        $event->job = $job;
        $event->student = $job->account;
        Event::trigger(self::className(), self::EVENT_JOB_CLOSED, $event);
    }

    public function eventJobOpened(Job $job)
    {
        $event = new NotificationEvent();
        $event->job = $job;
        $event->student = $job->account;
        Event::trigger(self::className(), self::EVENT_JOB_OPENED, $event);
    }

    public function eventNewMessageTutor($student, $tutor, $message, $messageObject, $chatMessageModel)
    {
        $event = new NotificationEvent();
        $event->student = $student;
        $event->tutor = $tutor;
        $event->message = $message;
        $event->messageObject = $messageObject;
        $event->messageModel = $chatMessageModel;
        Event::trigger(self::class, self::EVENT_NEW_MESSAGE_TUTOR, $event);
    }

    /**
     * @param Account $account
     * @param $missingInformation
     * @deprecated
     */
    public function eventMissingInformationTutor(Account $account, $missingInformation)
    {
        $event = new NotificationEvent();
        $event->account = $account;
        $event->missingInformation = $missingInformation;
        Event::trigger(self::className(), self::EVENT_MISSING_TUTOR_INFORMATION, $event);
    }

    public function triggerEvents()
    {
        return [
            'modules\account\events\trigger\AccountChangeEvent',
            'modules\account\events\trigger\HourlyRateChangeEvent',
            'modules\account\events\trigger\ProfileChangeEvent',
            'modules\account\events\trigger\RatingChangeEvent',
            'modules\account\events\trigger\SubjectChangeEvent',
            'modules\account\events\trigger\ReviewChangeEvent',
            'modules\account\events\trigger\ReviewDeletedEvent',
            'modules\account\events\trigger\LessonChangeEvent',
        ];
    }

    /**
     * @param null $zipCode
     * @return bool|string
     */
    public static function getTownNameByZipCode($zipCode = null)
    {
        if (!empty($zipCode)) {
            $zip = Yii::$app->db->cache(function () use ($zipCode) {
                return Zipcode::find()
                    ->andWhere(['code' => $zipCode])
                    ->joinWith('city')
                    ->limit(1)
                    ->one();
            });
            if (!empty($zip)) {
                return $zip->city->name;
            }
        }
        return ZipCodeHelper::getCityByUserIP();
    }

    /**
     * @param bool $static
     * @param array $config
     * @return Account
     * @throws \yii\base\InvalidConfigException
     */
    public function getAccountModel($static = false, $config = [])
    {
        $modelName = 'Account';
        return $static
            ? $this->model($modelName, $config)
            : $this->modelStatic($modelName);
    }

    /**
     * @param bool $static
     * @param array $config
     * @return Job
     * @throws \yii\base\InvalidConfigException
     */
    public function getJobModel($static = false, $config = [])
    {
        $modelName = 'Job';
        return $static
            ? $this->model($modelName, $config)
            : $this->modelStatic($modelName);
    }
}

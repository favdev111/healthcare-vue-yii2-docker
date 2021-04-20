<?php

namespace modules\account\models;

use ArrayObject;
use common\components\Formatter;
use common\components\UploadTrait;
use common\components\validators\MailRuValidator;
use common\helpers\AccountStatusHelper;
use common\helpers\Url;
use common\models\AccountTerms;
use common\models\ClientChild;
use common\models\ProcessedEvent;
use common\models\query\HealthProfileQuery;
use common\models\Review;
use modules\account\helpers\ConstantsHelper;
use modules\account\helpers\EventHelper;
use modules\account\models\api\AccountEmployee;
use modules\account\models\api\AccountTeam;
use modules\account\models\api2Patient\entities\healthProfile\HealthProfile;
use modules\account\models\ar\AccountAutoimmuneDisease;
use modules\account\models\ar\AccountHealthGoal;
use modules\account\models\ar\AccountHealthTest;
use modules\account\models\ar\AccountInsuranceCompany;
use modules\account\models\ar\AccountLanguage;
use modules\account\models\ar\AccountLicenceState;
use modules\account\models\ar\AccountMedicalCondition;
use modules\account\models\ar\AccountReward;
use modules\account\models\ar\AccountSymptom;
use modules\account\models\ar\AccountTelehealthState;
use common\models\health\AutoimmuneDisease;
use common\models\health\HealthGoal;
use common\models\health\HealthTest;
use modules\account\models\ar\Language;
use common\models\health\MedicalCondition;
use common\models\health\Symptom;
use modules\account\models\query\AccountQuery;
use modules\account\models\SubjectOrCategory\AccountSubjectOrCategory;
use modules\chat\models\Chat;
use common\components\StringHelper as CommonStringHelper;
use modules\chat\models\ChatMessage;
use modules\labels\models\LabelRelationModel;
use modules\notification\activeQuery\api2\NotificationQuery;
use modules\notification\models\entities\common\Notification;
use modules\payment\models\CardInfo;
use modules\payment\models\DeclineCharge;
use modules\payment\models\PaymentCustomer;
use modules\payment\models\query\TransactionQuery;
use modules\payment\models\Transaction;
use tuyakhov\notifications\NotifiableInterface;
use tuyakhov\notifications\NotifiableTrait;
use Yii;
use common\components\ActiveRecord;
use yii\base\Exception;
use yii\base\InvalidCallException;
use yii\db\ActiveQuery;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\ConflictHttpException;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "account".
 *
 * @property string $id
 * @property string $publicId
 * @property string $email
 * @property string $passwordHash
 * @property integer $status
 * @property string $banReason
 * @property boolean $isEmailConfirmed
 * @property integer $roleId
 * @property string $createdAt
 * @property string $updatedAt
 * @property string $flagDate
 * @property integer $createdIp
 * @property integer $searchHide Hide profile from marketpace and search index
 * @property integer $hideProfile Use to hide profile from landing pages
 * @property integer $commission
 * @property integer $clientInvited
 * @property integer $paymentProcessType
 * @property boolean $receivePaymentsToPlatformAccount
 * @property integer $profileUniqueWordsCount
 * @property integer $registrationStep
 * @property boolean $hasPhoto
 *
 * @property Profile $profile
 * @property AccountAvailability $availability
 * @property CardInfo[] $cardInfo
 * @property AccountSubject[] $subjects
 * @property PaymentCustomer $paymentCustomer
 * @property Role $role
 * @property AccountTerms $terms
 * @property Token[] $tokens
 * @property Education[] $educations
 * @property Transaction[] $transferTransactions
 * @property EducationCollege[] $colleges
 * @property Chat $chat
 * @property Job[] $jobs
 * @property AccountRating $rating
 * @property Rate $rate
 * @property AccountClientStatistic $clientStatistic
 * @property Account[] $addedAccounts
 * @property PaymentAccount $paymentAccount
 * @property Account[] $clients
 * @property ClientChild[] $children
 * @property [] $subjectsOrCategories
 * @property Account $employee
 * @property integer[] $listClientsIdsRelatedToEmployee
 * @property Account $processAccount
 * @property AccountPhone[] $accountPhones
 * @property AccountPhone $accountMainPhoneNumber
 * @property AccountEmail $accountEmails
 * @property AccountTeam $accountTeam
 * @property string $totalRating
 * @property Subject[] $accountSubjects
 * @property int $blockReason
 * @property AccountScore $score
 * @property AccountInsuranceCompany[] $accountInsurance
 * @property-read \yii\db\ActiveQuery $accessTokens
 * @property-read mixed $contentScore
 * @property-read null $topTutorSubjectByLessonCount
 * @property-read mixed $teachHoursScore
 * @property-read mixed $healthGoals
 * @property-read array $readyForTransferTransactionIds
 * @property-read mixed $medicalConditions
 * @property-read mixed $telehealthStates
 * @property-read AccountReward[] $certifications
 * @property-read bool|mixed $firstCollegeName
 * @property-read bool|mixed|float|int $fillBalanceAmount
 * @property-read string $fullName
 * @property-read \yii\db\ActiveQuery $socialAuths
 * @property-read string $totalPunctual
 * @property-read mixed $languages
 * @property-read string $authKey
 * @property-read \yii\db\ActiveQuery $listAddedAccount
 * @property-read mixed $autoimmuneDiseases
 * @property-read \yii\db\ActiveQuery $tutorLessons
 * @property-read \yii\db\ActiveQuery $labelRelation
 * @property-read string|mixed $statusName
 * @property-read \yii\db\ActiveQuery $declineCharges
 * @property-read array $idsOfRelatedCategories
 * @property-read mixed $healthTests
 * @property-read string $displayName
 * @property-read \yii\db\ActiveQuery $listAddedAccountChats
 * @property-read string $profileUrl
 * @property-read \yii\db\ActiveQuery $rewards
 * @property-read float $sumForTransfer
 * @property-read \yii\db\ActiveQuery $reviewEmail
 * @property-read string $roleName
 * @property-read null|\modules\payment\models\CardInfo $activeCardInfo
 * @property-read string|int $countSubjects
 * @property-read ArrayObject $categorySubject
 * @property-read mixed $symptoms
 * @property-read \yii\db\ActiveQuery $studentLessons
 * @property-read int $totalTeachHours
 * @property-read string $totalProficiency
 * @property-read string $imageName
 * @property-read AccountLicenceState[] $licenceStates
 * @property-read \yii\db\ActiveQuery $healthProfiles
 * @property-read string $totalArticulation
 * @property-read int $totalStudents
 * @property-read \yii\db\ActiveQuery $employeeAccount
 * @property-read \yii\db\ActiveQuery $review
 * @property-read \yii\db\ActiveQuery $reviews
 * @property-read null|mixed $responseTimeScore
 * @property-read int $countReview
 * @property-read mixed $ratingScore
 * @property-read null|float $avgResponseTime
 * @property-read string $bio
 * @property-read \yii\db\ActiveQuery $employeeClients
 * @property-read \yii\db\ActiveQuery $notes
 * @property-read array $subjectsOrCategories
 * @property HealthProfile $mainHealthProfile
 *
 * @property Notification[] $notificaitons
 * @property Notification[] $unreadNotifications
 */
class Account extends ActiveRecord implements IdentityInterface, NotifiableInterface
{
    use ChangeLogTrait;
    use UploadTrait;
    use NotifiableTrait;

    const SEE_ALL_ACCOUNT_WORDS_LIMIT = 100;

    const PAYMENT_TYPE_USUAL = 0;
    const PAYMENT_TYPE_BATCH_PAYMENT = 1;
    const PAYMENT_TYPE_PLATFORM_ACCOUNT = 2;

    /**
     * Inactive account email
     */
    const BLOCK_REASON_INACTIVE = 1;

    /**
     *
     */
    const COMMISSION_ZERO = 0;
    /**
     *
     */
    const COMMISSION_FIVE = 5;
    /**
     *
     */
    const COMMISSION_FIFTEEN = 15;

    /**
     *
     */
    const PRIMARY_KEY = 'id';

    /**
     *
     */
    public const SUSPENDED_STATUSES = [
        AccountStatusHelper::STATUS_DELETED,
        AccountStatusHelper::STATUS_BLOCKED
    ];

    /**
     *
     */
    const SCENARIO_REGISTER = 'register';
    const SCENARIO_SING_UP_SPECIALIST = 'specialist_sign_up_1_step';
    const SCENARIO_UPDATE_SPECIALIST = 'update_specialist';
    /**
     *
     */
    const SCENARIO_UPDATE_EMPLOYEE = 'updateEmployee';

    /**
     * @var integer Amount of unread notifications
     */
    protected $unreadNotifications;

    /**
     * @var string Comment for next created change log instance
     */
    public $changeLogComment;

    /**
     * @var string New password - for registration and changing password
     */
    public $newPassword;

    /**
     * @var array Permission cache array
     */
    protected $permissionCache = [];

    protected static $accountTeamClass = AccountTeam::class;

    public $accessToken;

    public function getImageName()
    {
        return $this->publicId;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account}}';
    }

    /**
     *
     */
    public function init()
    {
        $this->module = Yii::$app->getModuleAccount();
    }

    /**
     * @return array
     */
    public static function rulesCommon()
    {
        return [
            [
                'email',
                'email',
                'checkDNS' => true,
                'when' => function ($model) {
                    if ($model instanceof BaseActiveRecord) {
                        return $model->isAttributeChanged('email');
                    }
                    return true;
                }
            ],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'string', 'max' => 255],
            ['childrenData', 'safe']
        ];
    }

    /**
     * @return array
     */
    public function getSubjectsOrCategories(): array
    {
        $resultArray = [];
        $accountSubjectsOrCategories = AccountSubjectOrCategory::find()->andWhere(['accountId' => $this->id])->all();
        if (!empty($accountSubjectsOrCategories)) {
            foreach ($accountSubjectsOrCategories as $accountSubjectOrCategory) {
                $resultArray[] = $accountSubjectOrCategory->getSubjectOrCategory();
            }
        }
        return $resultArray;
    }

    /**
     * @return ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(ClientChild::class, ['accountId' => 'id']);
    }

    /**
     * @return array
     */
    public function rules()
    {
        // set initial rules
        $rules = [
            // general email rules
            [['email'], 'required'],
            [['email'], 'string', 'max' => 255],
            [['email'], MailRuValidator::class],
            [
                ['email'],
                'unique',
                'filter' => function ($query) {
                    /**
                     * @var $query AccountQuery
                     */
                    $query->andNonSuspended();
                }, 'on' => [self::SCENARIO_SING_UP_SPECIALIST]
            ],
            [
                ['email'],
                'unique',
                'filter' => function ($query) {
                    /**
                     * @var $query AccountQuery
                     */
                    $query->andNonSuspended();
                    $query->andWhere(['not', ['email' => $this->email]]);
                }, 'on' => [self::SCENARIO_UPDATE_SPECIALIST]
            ],
            // password rules
            [['newPassword'], 'string', 'min' => 6],
            [['newPassword'], 'required', 'on' => [static::SCENARIO_REGISTER, 'reset', self::SCENARIO_SING_UP_SPECIALIST]],
        ];

        return array_merge(
            static::rulesCommon(),
            $rules
        );
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['reset'] = ['newPassword'];
        return $scenarios;
    }

    /**
     * @return string
     */
    public function getProfileUrl(): string
    {
        return Url::to(['/account/profile-tutor/tutor-info', 'id' => $this->id]);
    }

    public function setBlockedReasonInactive()
    {
        $this->blockReason = static::BLOCK_REASON_INACTIVE;
    }

    public function getAccountInsurance()
    {
        return $this->hasMany(AccountInsuranceCompany::class, ['accountId' => 'id']);
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'roleId' => 'Role ID',
            'email' => 'Email',
            'newPassword' => $this->isNewRecord ? 'Password' : 'New Password',
            'displayName' => 'Full name',
        ];
    }

    /**
     * @return array
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
     * @return ActiveQuery
     */
    public function getProfile()
    {
        $profileClassName = $this->module->modelStatic('Profile');
        return $this->hasOne($profileClassName, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTerms()
    {
        return $this->hasOne(AccountTerms::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getSocialAuths()
    {
        return $this->hasMany(AccountSocialAuth::class, ['accountId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccessTokens()
    {
        return $this->hasMany(AccountAccessToken::class, ['accountId' => 'id']);
    }

    public function getLicenceStates()
    {
        return $this->hasMany(AccountLicenceState::class, ['accountId' => 'id']);
    }

    public function getTelehealthStates()
    {
        return $this->hasMany(AccountTelehealthState::class, ['accountId' => 'id']);
    }

    public function getCertifications()
    {
        return $this->hasMany(AccountReward::class, ['accountId' => 'id']);
    }

    public function getHealthTests()
    {
        return $this->hasMany(HealthTest::class, ['id' => 'healthTestId'])
            ->viaTable(AccountHealthTest::tableName(), ['accountId' => 'id']);
    }

    public function getSymptoms()
    {
        return $this->hasMany(Symptom::class, ['id' => 'symptomId'])
            ->viaTable(AccountSymptom::tableName(), ['accountId' => 'id']);
    }

    public function getMedicalConditions()
    {
        return $this->hasMany(MedicalCondition::class, ['id' => 'medicalConditionId'])
            ->viaTable(AccountMedicalCondition::tableName(), ['accountId' => 'id']);
    }

    public function getAutoimmuneDiseases()
    {
        return $this->hasMany(AutoimmuneDisease::class, ['id' => 'autoimmuneDiseaseId'])
            ->viaTable(AccountAutoimmuneDisease::tableName(), ['accountId' => 'id']);
    }

    public function getHealthGoals()
    {
        return $this->hasMany(HealthGoal::class, ['id' => 'healthGoalId'])
            ->viaTable(AccountHealthGoal::tableName(), ['accountId' => 'id']);
    }

    public function getLanguages()
    {
        return $this->hasMany(Language::class, ['id' => 'languageId'])
            ->viaTable(AccountLanguage::tableName(), ['accountId' => 'id']);
    }

    /**
     * @return bool
     */
    public function isTermsSigned(): bool
    {
        if (empty($this->terms)) {
            return false;
        }

        return (bool)$this->terms->termsSigned;
    }

    /**
     * @return array
     */
    public function getListClientsIdsRelatedToEmployee()
    {
        return EmployeeClient::find()
            ->andWhere(['employeeId' => $this->id])
            ->select('clientId')
            ->column();
    }

    /**
     * @return ActiveQuery
     */
    public function getAvailability()
    {
        return $this->hasOne(AccountAvailability::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getListAddedAccount()
    {
        if ($this->isTutor()) {
            return $this->hasMany(ListAddedAccount::class, ['ownerId' => 'id']);
        }
        return $this->hasMany(ListAddedAccount::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getClientStatistic()
    {
        return $this->hasOne(AccountClientStatistic::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAddedAccounts()
    {
        if ($this->isTutor()) {
            return $this->hasMany(self::class, ['id' => 'accountId'])->via('listAddedAccount');
        }
        return $this->hasMany(self::class, ['id' => 'ownerId'])->via('listAddedAccount');
    }

    /**
     * @return ActiveQuery
     */
    public function getJobs()
    {
        return $this->hasMany(Job::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getListAddedAccountChats()
    {
        return $this->hasMany(Chat::class, ['accountId' => 'accountId'])->via('listAddedAccount');
    }

    /**
     * for tutor
     * @return ActiveQuery
     */
    public function getDeclineCharges()
    {
        return $this->hasMany(DeclineCharge::class, ['tutorId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRole()
    {
        $role = $this->module->modelStatic('Role');
        return $this->hasOne($role, ['id' => 'roleId']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTokens()
    {
        $token = $this->module->modelStatic('Token');
        return $this->hasMany($token, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getReviews()
    {
        return $this->hasMany(Review::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTutorLessons()
    {
        return $this->hasMany(Lesson::class, ['tutorId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getStudentLessons()
    {
        return $this->hasMany(Lesson::class, ['studentId' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($tokenString, $type = null)
    {
        /** @var Token $tokenModel */
        $tokenModel = Yii::$app->getModule('account')->model('token');
        $tokenData = $tokenModel::verifyJwtToken($tokenString);

        if (false === $tokenData) {
            return null;
        }

        $token = $tokenModel::findByToken($tokenData['token'], $tokenModel::TYPE_TOKEN);
        if (
            !$token
            || ((int)$token->accountId !== $tokenData['accountId'])
        ) {
            return null;
        }

        if ($token) {
            return $token->account;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->publicId;
    }

    /**
     * @return float
     */
    public function getSumForTransfer(): float
    {
        if ($this->isTutor() === false) {
            throw new InvalidCallException('Method can be used only by user with tutor role');
        }
        return (double)$this->getTransferTransactions()->sum('transaction.amount');
    }

    /**
     * get ids of charges need process
     * @return array
     */
    public function getReadyForTransferTransactionIds(): array
    {
        return ArrayHelper::getColumn(
            Transaction::find()
                ->select(Transaction::tableName() . '.id')
                ->byReadyForTransfer()
                ->byTutor($this)
                ->asArray()->all(),
            'id'
        );
    }


    /**
     * @return int
     */
    public function updateProcessDate(): int
    {
        return Transaction::updateAll(
            [
                'processDate' => date('Y-m-d', strtotime('+1 day', time())),
            ],
            [
                'in',
                'id',
                $this->getReadyForTransferTransactionIds()
            ]
        );
    }

    /**
     * @return bool
     */
    public function isTransferAvailable(): bool
    {
        if ($this->isTutor() === false) {
            throw new InvalidCallException('Method can be used only by user with tutor role');
        }

        $paymentAccount = $this->paymentAccount;

        if (!$paymentAccount) {
            return false;
        }

        $lastTransfer = Transaction::find()->byLastTutorTransfer($this)->limit(1)->one();

        if (!$lastTransfer || $lastTransfer->isStatusError() === false) {
            return true;
        }

        $availableBalance = $paymentAccount->getAvailableAmount();
        return $availableBalance >= $lastTransfer->amount;
    }

    /**
     * @return bool
     */
    public function isStatusBlocked(): bool
    {
        return $this->status === AccountStatusHelper::STATUS_BLOCKED;
    }

    /**
     * @return bool
     */
    public function isStatusActive(): bool
    {
        return $this->status == AccountStatusHelper::STATUS_ACTIVE;
    }

    /**
     * @param string $authKey
     * @return bool
     */
    public function validateAuthKey($authKey): bool
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validate password
     * @param string $password
     * @return bool
     */
    public function validatePassword($password): bool
    {
        if (empty($this->passwordHash)) {
            return false;
        }

        return Yii::$app->security->validatePassword($password, $this->passwordHash);
    }

    protected function isUnique(): bool
    {
        return !self::find()
            ->andNonSuspended()
            ->andWhere(['email' => $this->email])
            ->exists();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert && !$this->isUnique()) {
            throw new ConflictHttpException('Email already registered.');
        }

        if ($insert || (empty($this->publicId))) {
            $this->publicId = Yii::$app->security->generateRandomString();
            if (!$this->createdIp) {
                $this->createdIp = Yii::$app->request->userIP;
            }
            $this->hideProfile = false;
        }

        if ($insert && !$this->status) {
            $this->status = AccountStatusHelper::STATUS_CREATED;
        }

        if ($this->isAttributeChanged('flag')) {
            /**
             * @var Formatter $formatter
             */
            $formatter = \Yii::$app->formatter;
            $this->flagDate = $formatter->asDate(time(), 'php:' . $formatter->MYSQL_DATETIME);
        }

        if ($this->isAttributeChanged('status') && $this->isStatusActive()) {
            $this->blockReason = null;
        }

        // hash new password if set
        if ($this->newPassword) {
            $this->passwordHash = self::generatePasswordHash($this->newPassword);
        }

        return parent::beforeSave($insert);
    }

    /**
     * @param $password
     * @return string
     * @throws Exception
     */
    public static function generatePasswordHash($password)
    {
        return Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        EventHelper::changeAccountEvent(
            $this,
            $insert,
            $changedAttributes
        );

        if (
            $insert && ($this->isCompanyEmployee() || $this->isCrmAdmin())
        ) {
            $auth = Yii::$app->authManager;
            //assign rbac roles for employee in AccountTeam model
            if ($this->roleId !== Role::ROLE_COMPANY_EMPLOYEE) {
                $auth->assign($auth->getRole($this->roleId), $this->id);
            }
        }

        if ($this->isCompanyEmployee() || $this->isPatient()) {
            $this->processEmployeeClientRelations($changedAttributes);
        }

        if ($insert && $this->isPatient()) {
            $this->link('mainHealthProfile', new HealthProfile([
                'isMain' => true,
            ]));
        }

        if (!$insert) {
            $this->changeLog(
                'flag',
                FlagChangeLog::class,
                $this->id,
                $changedAttributes,
                $this->changeLogComment ?? ''
            );
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @param $changedAttributes
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function processEmployeeClientRelations($changedAttributes)
    {
        //if status changed to block or delete - remove relation between client and employee
        if (array_key_exists('status', $changedAttributes) && $this->isSuspended()) {
            $field = $this->isCompanyEmployee() ? 'employeeId' : 'clientId';
            //delete relations with clients (tasks)
            $employeeClientsRelations = EmployeeClient::find()->andWhere([$field => $this->id]);
            foreach ($employeeClientsRelations->all() as $relation) {
                $relation->delete();
            }
        }
    }

    /**
     * Can current account edit some company admin account
     * @param Account $companyAdminAccount
     * @return bool
     */
    public function isCanEditAdmin($companyAdminAccount)
    {
        //only company owner can change admin profile
        return $this->isCrmAdmin()
            //only admins of own company
            && $companyAdminAccount->isAdminOf($this->id);
    }

    /**
     * @param $newEmail
     * @param $oldEmail
     * @return bool
     * @throws \Throwable
     */
    public function confirm($newEmail, $oldEmail)
    {
        $this->isEmailConfirmed = true;

        if ($newEmail) {
            $checkUser = static::findOne(['email' => $newEmail]);
            if ($checkUser) {
                return false;
            } else {
                try {
                    $this->updateAccountEmailItem($oldEmail, $newEmail);
                } catch (\Exception $exception) {
                    return false;
                }
                $this->email = $newEmail;
            }
        }

        $this->save(false, ['email', 'isEmailConfirmed', 'status']);

        return true;
    }

    /**
     * Check if user can do specified $permission
     * @param string $permissionName
     * @param array $params
     * @param bool $allowCaching
     * @return bool
     */
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        // check for auth manager rbac
        // copied from \yii\web\User
        $auth = Yii::$app->getAuthManager();
        if ($auth) {
            if ($allowCaching && empty($params) && isset($this->permissionCache[$permissionName])) {
                return $this->permissionCache[$permissionName];
            }
            $access = $auth->checkAccess($this->getId(), $permissionName, $params);
            if ($allowCaching && empty($params)) {
                $this->permissionCache[$permissionName] = $access;
            }
            return $access;
        }

        // otherwise use our own custom permission (via the role table)
        return $this->roleId === $permissionName;
    }

    /**
     * Get display name for the user
     * @return string
     */
    public function getDisplayName()
    {
        $profile = $this->profile;
        return $profile->showName;
    }

    /**
     * Using full name only in specific cases like Tutor Profile meta
     * setting it as internal to prevent its usage everywhere
     * @return string
     * @internal
     */
    public function getFullName()
    {
        return $this->profile->fullName;
    }


    /**
     * @return bool
     */
    public function isActive()
    {
        return !$this->isSuspended();
    }

    /**
     * @return bool
     */
    public function isSuspended()
    {
        return in_array($this->status, self::SUSPENDED_STATUSES);
    }

    /**
     * @return ArrayObject
     */
    public function getCategorySubject()
    {
        $categories = [];
        $subjects = [];
        foreach ($this->subjects as $accSubject) {
            foreach ($accSubject->subject->categories as $category) {
                $categoryId = $category->id;
                if (!array_key_exists($categoryId, $categories)) {
                    $categories[$categoryId] = $category;
                }

                if (array_key_exists($categoryId, $subjects)) {
                    array_push($subjects[$categoryId], $accSubject->subject);
                } else {
                    $subjects[$categoryId] = [$accSubject->subject];
                }
            }
        }

        return new ArrayObject([
            'categories' => $categories,
            'subjects' => $subjects,
        ], ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * @return array
     */
    public function getIdsOfRelatedCategories()
    {
        $result = [];
        $models = $this->getSubjectsOrCategories();
        if (!empty($models)) {
            foreach ($models as $model) {
                $valueToAdd = $model->isCategory() ? $model->getId() : $model->getModel()->category->id;
                if (!in_array($valueToAdd, $result)) {
                    $result[] = $valueToAdd;
                }
            }
        }
        return $result;
    }


    /**
     * @param string $oldEmail
     * @param string $newEmail
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function updateAccountEmailItem(string $oldEmail, string $newEmail): void
    {
        $accountEmail = $this->getAccountEmails()
            ->andWhere(['email' => $oldEmail])
            ->andWhere(['accountId' => $this->id])
            ->one();
        if ($accountEmail) {
            $accountEmail->email = $newEmail;
            $accountEmail->update(false);
        }

        $oldAdditionalEmail = $this->getAccountEmails()
            ->andWhere(['email' => $newEmail])
            ->andWhere(['accountId' => $this->id])
            ->andWhere(['isPrimary' => false])
            ->one();

        if (!empty($oldAdditionalEmail)) {
            $oldAdditionalEmail->delete();
        }
    }


    /**
     * @param string $newPhone
     * @param string $oldPhone
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function updateAccountPhoneItem(string $newPhone, string $oldPhone): void
    {
        $accountPhoneNumber = $this->getAccountPhones()
            ->andWhere(['phoneNumber' => $oldPhone])
            ->andWhere(['accountId' => $this->id])
            ->one();
        if ($accountPhoneNumber) {
            $accountPhoneNumber->phoneNumber = $newPhone;
            $accountPhoneNumber->update(false);
        }
    }

    /**
     * @return int|string
     */
    public function getCountSubjects()
    {
        return $this->getSubjects()->innerJoinWith('subject.categories')->count();
    }

    /**
     * @return ActiveQuery
     */
    public function getRate()
    {
        return $this->hasOne(Rate::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getMainHealthProfile()
    {
        return $this->hasOne(HealthProfile::class, ['accountId' => 'id'])
            ->andOnCondition(['isMain' => true]);
    }

    /**
     * @return HealthProfile[]|HealthProfileQuery
     */
    public function getHealthProfiles()
    {
        return $this->hasMany(HealthProfile::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountSubjects()
    {
        return $this->hasMany(Subject::class, ['id' => 'subjectId'])
            ->viaTable(AccountSubject::tableName(), ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountEmails()
    {
        return $this->hasMany(AccountEmail::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountPhones()
    {
        return $this->hasMany(AccountPhone::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountMainPhoneNumber()
    {
        return $this->hasOne(AccountPhone::class, ['accountId' => 'id'])->andWhere([AccountPhone::tableName() . '.isPrimary' => true]);
    }

    /**
     * @return ActiveQuery
     */
    public function getEmployeeClients()
    {
        return $this->hasMany(EmployeeClient::class, ['employeeId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(EmployeeClient::class, ['clientId' => 'id']);
    }

    /**
     * Relation for employee
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeAccount()
    {
        return $this->hasOne(AccountEmployee::class, ['id' => 'id'])->viaTable(EmployeeClient::tableName(), ['clientId' => 'employeeId']);
    }

    /**
     * @return ActiveQuery
     */
    public function getLabelRelation()
    {
        return $this->hasOne(LabelRelationModel::class, ['itemId' => 'id']);
    }

    /**
     * @return TransactionQuery
     */
    public function getTransferTransactions()
    {
        return $this->hasMany(Transaction::class, ['tutorId' => 'id'])->byReadyForTransfer();
    }

    /**
     * @return ActiveQuery
     */
    public function getSubjects()
    {
        return $this->hasMany(AccountSubject::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNotes()
    {
        return $this->hasMany(AccountNote::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getEducations()
    {
        return $this->hasMany(Education::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRewards()
    {
        return $this->hasMany(Reward::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRating()
    {
        return $this->hasOne(AccountRating::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getScore()
    {
        return $this->hasOne(AccountScore::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getReview()
    {
        return $this->hasMany(Review::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getReviewEmail()
    {
        return $this->getReview()->andWhere(['!=', 'isAdmin', true]);
    }

    /**
     * @return integer
     */
    public function getCountReview()
    {
        return $this->getReview()->andOnCondition([
            '!=',
            'isAdmin',
            true
        ])->andOnCondition(['status' => Review::ACTIVE])->count();
    }

    /**
     * @return string
     */
    public function getTotalRating()
    {
        $rating = $this->rating;
        return Yii::$app->formatter->asDecimal(isset($rating) ? $rating->totalRating : 0, 1);
    }

    /**
     * @return string
     */
    public function getTotalPunctual()
    {
        $rating = $this->rating;
        return Yii::$app->formatter->asDecimal(isset($rating) ? $rating->totalPunctual : 0, 1);
    }

    /**
     * @return string
     */
    public function getTotalProficiency()
    {
        $rating = $this->rating;
        return Yii::$app->formatter->asDecimal(isset($rating) ? $rating->totalProficiency : 0, 1);
    }

    /**
     * @return string
     */
    public function getTotalArticulation()
    {
        $rating = $this->rating;
        return Yii::$app->formatter->asDecimal(isset($rating) ? $rating->totalArticulation : 0, 1);
    }

    /**
     * @return int
     */
    public function getTotalTeachHours()
    {
        $rating = $this->rating;
        return isset($rating) ? $rating->totalHours : 0;
    }

    /**
     * @return float|null
     */
    public function getAvgResponseTime()
    {
        $rating = $this->rating;
        return isset($rating) ? $rating->avgResponseTime : null;
    }

    /**
     * @return int
     */
    public function getTotalStudents()
    {
        $rating = $this->rating;
        return isset($rating) ? $rating->totalAccounts : 0;
    }

    /**
     * @param $searchValue
     * @param $type
     * @return mixed
     */
    protected function getScores($searchValue, $type)
    {
        $searchValue = (double)$searchValue;
        $scores = $this->module->getScores($type);
        foreach ($scores as $score) {
            $key = $score['key'];

            if (mb_strpos($key, '+')) {
                $values = explode('+', $key);
                $value = (double)$values[0];
                if ($value <= $searchValue) {
                    return $score['value'];
                }
            } elseif (mb_strpos($key, '-')) {
                $values = explode('-', $key);
                $min = (double)$values[0];
                $max = (double)$values[1];
                if ($min <= $searchValue && $searchValue <= $max) {
                    return $score['value'];
                }
            } else {
                $value = (double)$key;
                if ($value == $searchValue) {
                    return $score['value'];
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getRatingScore()
    {
        $rating = $this->getTotalRating();
        return $this->getScores($rating, TutorScoreSettings::TYPE_RATING);
    }

    /**
     * @return mixed|null
     */
    public function getResponseTimeScore()
    {
        $responseTime = $this->getAvgResponseTime();
        if (is_null($responseTime)) {
            return null;
        }
        $responseTime = Yii::$app->formatter->asDecimal($responseTime / 3600, 1);
        return $this->getScores($responseTime, TutorScoreSettings::TYPE_RESPONSE_TIME);
    }

    /**
     * @return mixed
     */
    public function getContentScore()
    {
        $content = mb_strlen($this->profile->description);
        return $this->getScores($content, TutorScoreSettings::TYPE_CONTENT_PROFILE);
    }

    /**
     * @return mixed
     */
    public function getTeachHoursScore()
    {
        $teachHours = $this->getTotalTeachHours();
        return $this->getScores($teachHours, TutorScoreSettings::TYPE_HOURS);
    }

    /**
     * @param $type
     * @return float|string
     */
    public function getProgress($type)
    {
        $rating = $this->rating;
        if (!isset($rating)) {
            return Yii::$app->formatter->asDecimal(0, 1);
        }
        switch ($type) {
            case 'articulation':
                return $rating->totalArticulation / 0.05;
                break;
            case 'proficiency':
                return $rating->totalProficiency / 0.05;
                break;
            case 'punctual':
                return $rating->totalPunctual / 0.05;
                break;
            default:
                return Yii::$app->formatter->asDecimal(0, 1);
                break;
        }
    }

    /**
     * @return ActiveQuery
     */
    public function getCardInfo()
    {
        $model = Yii::$app->getModule('payment')->model('CardInfo');
        return $this->hasMany($model::className(), ['stripeCustomerId' => 'id'])
            ->viaTable(PaymentCustomer::tableName(), ['accountId' => 'id']);
    }

    /**
     * @return CardInfo | null
     * @todo: Bad solution. Getter should not change any data. task (HEYTUTOR-2861)
     * 1) We should create migration(or some over logic) for old account without active cards
     * 2) We should prevent situation when user has payment cards but all cards are not active
     */
    public function getActiveCardInfo()
    {
        $activeCard = $this->getCardInfo()
            ->andWhere([CardInfo::tableName() . '.active' => CardInfo::STATUS_ACTIVE])
            ->one();

        if (!$activeCard && $this->getCardInfo()->exists()) {
            $card = $this->getCardInfo()->one();
            $card->setStatusActive()->save(false);
            $activeCard = $card;
        }


        return $activeCard;
    }

    /**
     * @return ActiveQuery
     */
    public function getPaymentCustomer()
    {
        return $this->hasOne(PaymentCustomer::class, ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPaymentAccount()
    {
        $model = Yii::$app->getModule('payment')->model('Account');
        return $this->hasOne($model::className(), ['accountId' => 'id']);
    }

    /**
     * @return string
     */
    public function getBio()
    {
        return $this->profile->getDescription();
    }

    /**
     * @param integer $length
     * @return string
     */
    public function getTruncatedBio($length)
    {
        $description = $this->profile->getDescription();
        return CommonStringHelper::truncate($description, $length, '...', null, true);
    }

    /**
     * @return ActiveQuery
     */
    public function getColleges()
    {
        return $this->hasMany(EducationCollege::class, ['id' => 'collegeId'])
            ->viaTable(Education::tableName(), ['accountId' => 'id']);
    }

    /**
     * @return bool|mixed
     */
    public function getFirstCollegeName()
    {
        $model = $this->colleges;
        if ($model && $model[0]) {
            return $model[0]->fullName;
        }

        return false;
    }

    /**
     * @return Token
     * @throws Exception
     */
    public function generateToken()
    {
        // calculate expireTime
        $expireTime = $this->module->resetExpireTime;
        $expireTime = $expireTime ? gmdate("Y-m-d H:i:s", strtotime($expireTime)) : null;

        /** @var \modules\account\models\Token $tokenModel */
        $tokenModel = $this->module->model('Token');
        return $tokenModel::generate($this->id, $tokenModel::TYPE_PASSWORD_RESET, null, $expireTime);
    }

    public function generateInactiveAccountEmailToken(): Token
    {
        /** @var \modules\account\models\Token $tokenModel */
        $expireTime = date("Y-m-d H:i:s", strtotime('+2 days'));
        $tokenModel = $this->module->model('Token');
        $tokenModel = $tokenModel::generate($this->id, $tokenModel::TYPE_EMAIL_INACTIVE_ACCOUNT, null, $expireTime);
        if (!$tokenModel->isNewRecord) {
            $tokenModel->expiredAt = $expireTime;
            $tokenModel->save(false);
        }
        return $tokenModel;
    }

    /**
     * @return mixed|string
     */
    public function getStatusName()
    {
        return AccountStatusHelper::getStatusName($this->status);
    }

    /**
     * @param string $filter
     * @param null $subjectsIdFromJob
     * @param null $categoryIdsFromJob
     * @param bool $returnQuery
     * @return array|ActiveQuery|\yii\db\ActiveRecord[]
     */
    public function getTutorSubjectsOrCategories(
        string $filter = '',
        $subjectsIdFromJob = null,
        $categoryIdsFromJob = null,
        $returnQuery = false
    ) {
        $subjectIds = AccountSubjectOrCategory::find()->andWhere([
            'isCategory' => 0,
            'accountId' => $this->id
        ])->asArray()->all();

        $subjectIds = ArrayHelper::getColumn($subjectIds, 'subjectId');

        $tutorSubjectIdsCondition = ['in', Subject::tableName() . '.id', $subjectIds];
        $filterSubjectNameCondition = ['like', Subject::tableName() . '.name', $filter . '%', false];
        $idsFromJobCondition = ['in', Subject::tableName() . '.id', $subjectsIdFromJob];
        $idsCategoriesFomJobCondition = [Category::tableName() . '.id' => $categoryIdsFromJob];

        $subjects = Subject::find()
            ->andWhere($tutorSubjectIdsCondition)
            ->andWhere($filterSubjectNameCondition)
            ->joinWith('category');

        $isSubjectsIdsFromJob = !empty($subjectsIdFromJob);
        $isCategoryIdsFromJob = !empty($categoryIdsFromJob);

        if ($isSubjectsIdsFromJob && $isCategoryIdsFromJob) {
            $subjects->andWhere([
                'or',
                $idsFromJobCondition,
                $idsCategoriesFomJobCondition
            ]);
        } else {
            if ($isCategoryIdsFromJob || $isSubjectsIdsFromJob) {
                $subjects->andWhere(
                    $isCategoryIdsFromJob ? $idsCategoriesFomJobCondition : $idsFromJobCondition
                );
            }
        }

        $subjects->indexBy('id');

        return $returnQuery ? $subjects : $subjects->all();
    }

    /**
     * @return bool
     * @deprecated
     */
    public function isTutor()
    {
        return $this->roleId == Role::ROLE_SPECIALIST;
    }

    /**
     * @return bool
     */
    public function isSpecialist()
    {
        return $this->roleId == Role::ROLE_SPECIALIST;
    }

    /**
     * @return bool
     */
    public function isCompanyEmployee()
    {
        return $this->roleId == Role::ROLE_COMPANY_EMPLOYEE;
    }

    /**
     * @return bool
     */
    public function isCrmAdmin()
    {
        return $this->roleId == Role::ROLE_CRM_ADMIN;
    }

    /**
     * @return bool
     */
    public function isPatient()
    {
        return ($this->roleId == Role::ROLE_PATIENT);
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

    /**
     * @return string
     */
    public function getRoleName()
    {
        // Using static method instead of relation for optimization purposes.
        return Role::getRoleNameById($this->roleId);
    }

    /**
     * @return bool
     */
    public function isUnderReview(): bool
    {
        return $this->status === AccountStatusHelper::STATUS_NEED_REVIEW;
    }

    /**
     * @return bool
     */
    public function isEmailConfirmed()
    {
        return $this->isEmailConfirmed;
    }

    // Proxy-ing default methods as custom ones to allow getting suspended jobs too

    /**
     * @param $condition
     * @return $this
     */
    public static function findOneWithoutRestrictions($condition)
    {
        return self::findWithoutRestrictions()
            ->andWhere(['id' => $condition])
            ->limit(1)
            ->one();
    }

    /**
     * @return AccountQuery
     */
    public static function findWithoutRestrictions()
    {
        return new AccountQuery(static::class);
    }

    /**
     * @param $condition
     * @return AccountQuery
     */
    public static function findByConditionWithoutRestrictions($condition)
    {
        return self::findWithoutRestrictions()->andWhere($condition);
    }

    /**
     * @param $sql
     * @param array $params
     * @return AccountQuery
     */
    public static function findBySqlWithoutRestrictions($sql, $params = [])
    {
        $query = self::findWithoutRestrictions();
        $query->sql = $sql;

        return $query->params($params);
    }

    /**
     * @param $condition
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function findAllWithoutRestrictions($condition)
    {
        return self::findByConditionWithoutRestrictions($condition)->all();
    }

    /**
     * @param $query AccountQuery
     * @return AccountQuery
     */
    protected static function addNonDeletedCondition($query)
    {
        return $query->andNonSuspended();
    }

    /**
     * @inheritdoc
     */
    public static function findOne($condition)
    {
        $query = parent::findByCondition($condition);
        static::addNonDeletedCondition($query);
        return $query->one();
    }

    /**
     * @param $id
     * @return array|\yii\db\ActiveRecord|null
     */
    public static function findWithAllStatus($id)
    {
        $query = parent::find()->andWhere(['id' => $id]);
        return $query->one();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        $query = new AccountQuery(static::class);
        static::addNonDeletedCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findByCondition($condition)
    {
        $query = parent::findByCondition($condition);
        static::addNonDeletedCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findBySql($sql, $params = [])
    {
        $query = parent::findBySql($sql, $params);
        static::addNonDeletedCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findAll($condition)
    {
        $query = parent::findByCondition($condition);
        static::addNonDeletedCondition($query);
        return $query->all();
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->status === AccountStatusHelper::STATUS_DELETED;
    }

    /**
     * @return bool|false|int
     */
    public function delete()
    {
        $this->status = AccountStatusHelper::STATUS_DELETED;
        if ($this->isSpecialist() && $this->getTutorLessons()->exists()) {
            // Delete prohibited for tutors with lessons
            return false;
        }

        if (
            $this->isPatient()
            && (
                $this->getJobs()->exists()
                || $this->getStudentLessons()->exists()
            )
        ) {
            // Delete prohibited for students with jobs
            return false;
        }

        return $this->save(true, ['status']);
    }

    /**
     * @return bool
     */
    public function isVerified()
    {
        if ($this->isPatient()) {
            return true;
        }
        return !empty($this->cardInfo);
    }

    /**
     * @return bool|float|int|mixed
     */
    public function getFillBalanceAmount()
    {
        if (!$this->isPatient()) {
            return false;
        }

        $amountToBeProcessed = Transaction::find()
            ->andWhere(['studentId' => $this->id])
            ->andWhere(['tutorId' => null])
            ->andWhere(['objectId' => $this->id])
            ->clientBalance()
            ->andWhere(['type' => Transaction::STRIPE_CHARGE])
            ->andWhere(['status' => Transaction::STATUS_NEW])
            ->sum('amount');

        if (!$this->paymentCustomer || !$this->paymentCustomer->packagePrice) {
            // Deduct non processed amount from current negative balance
            return ((-1 * $this->clientStatistic->balance) - $amountToBeProcessed) ?? 0;
        }
        $currentAmountToBeFilled = (-1 * $this->clientStatistic->balance);
        if ($amountToBeProcessed < $currentAmountToBeFilled) {
            $filledProcess = $currentAmountToBeFilled - $amountToBeProcessed;
            $numberOfPackages = $filledProcess / $this->paymentCustomer->packagePrice;
            $numberOfPackages = ceil($numberOfPackages);
            return $this->paymentCustomer->packagePrice * $numberOfPackages;
        }
        return 0;
    }

    /**
     * @param $account
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getChatLastMessageWith($account)
    {
        return ChatMessage::find()->andWhere([
            'or',
            [
                'and',
                ['sender_id' => $account->chat->chatUserId],
                ['recipient_id' => $this->chat->chatUserId],
            ],
            [
                'and',
                ['sender_id' => $this->chat->chatUserId],
                ['recipient_id' => $account->chat->chatUserId],
            ],
        ])
            ->orderBy(['createdAt' => SORT_DESC])
            ->limit(1)
            ->one();
    }

    /**
     * @param $account
     * @return array
     */
    public function getChatLastMessage($account)
    {
        $lastMessage = $this->getChatLastMessageWith($account);
        if (!$lastMessage) {
            return [];
        }

        $read = $lastMessage->messageRecipient->id === $this->id ? $lastMessage->recipientStatusRead : true;
        return [
            'text' => $lastMessage->message,
            'read' => $lastMessage->messageRecipient->id === $this->id ? (bool)$lastMessage->recipientStatusRead : true,
        ];
    }

    /**
     * @return bool
     */
    public function isZeroCommissionCompany(): bool
    {
        return $this->isCrmAdmin() && $this->commission === self::COMMISSION_ZERO;
    }

    /**
     *
     */
    public function setActiveLessonsStatus(): void
    {
        if ($this->isPatient()) {
            $isExistJobWithoutLesson = Job::find()
                ->andWhere(['studentId' => $this->id])
                ->joinWith('lessons', false, 'RIGHT JOIN')
                ->andWhere([Lesson::tableName() . '.id' => null])->exists();
            if (!$isExistJobWithoutLesson) {
                $statistic = $this->clientStatistic;
                $statistic->clientLessonStatus = ConstantsHelper::LESSON_STATUS__ACTIVE_LESSON;
                $statistic->save(false);
            }
        };
    }

    /**
     * @param int $type
     * @param int|null $jobId
     * @return bool
     */
    public function isProcessedEvent(int $type, int $jobId = null): bool
    {
        $query = ProcessedEvent::find()
            ->accountId($this->id)
            ->type($type);
        if (!empty($jobId)) {
            $query->job($jobId);
        }
        return $query->exists();
    }

    /**
     * @return bool
     */
    public function isPaymentMethodBankAccount(): bool
    {
        return $this->paymentCustomer->getActiveBankAccount()->exists();
    }

    /**
     * @return bool
     */
    public function isPaymentMethodCard(): bool
    {
        return $this->paymentCustomer->getActiveCard()->exists();
    }

    /**
     * @param int $type
     * @param int|null $jobId
     */
    public function addProcessedEvent(int $type, int $jobId = null)
    {
        $processedEvent = new ProcessedEvent();
        $processedEvent->jobId = $jobId;
        $processedEvent->accountId = $this->id;
        $processedEvent->type = $type;
        $processedEvent->save(false);
    }

    /**
     * @param Job $job
     * @param bool $isAutomatchJob
     * @return string
     */
    public function getContactInfoHtml(Job $job, bool $isAutomatchJob = false)
    {
        //build string with children names
        $childrenNames = [];
        foreach ($this->getChildren()->all() as $child) {
            $childrenNames[] = $child->fullName;
        }
        $childrenNames = implode(', ', $childrenNames);
        $subjects = implode(', ', $job->getSubjectOrCategoryNamesArray());

        if ($isAutomatchJob || $job->isAutomatchEnabled) {
            $city = $this->profile->city;
            $timeZone = $city->getTimeZone(true, true, true);
            $address = "<b>Time zone:</b> {$city->name}, {$city->stateNameShort} ({$timeZone})<br>";
        } else {
            $address = "<b>Address:</b> {$this->profile->address}<br>";
        }

        $showName = $this->profile->getShowName(null, false);
        return "<br><br><b>Here's the contact information and address for {$showName}:</b><br>"
            . "<br><b>Contact Name:</b> {$this->profile->fullName()}<br>"
            . $address
            . "<b>Phone:</b> {$this->profile->getFormattedPhone()}<br>"
            . "<b>Email:</b> {$this->email}<br>"
            . (!empty($childrenNames) ? "<b>Student Name:</b> {$childrenNames}<br>" : '')
            . "<b>Grade:</b> {$job->getStudentGradeText()}<br>"
            . "<b>Subject:</b> {$subjects}<br>"
            . '<br>Remember to contact your client right away. Thank you!';
    }

    /**
     * Has user access to forgot and login actions or not
     * @return bool
     */
    public function isClientInvited(): bool
    {
        return $this->isPatient() && $this->clientInvited;
    }

    public function isPaymentTypePlatformAccount(): bool
    {
        return $this->paymentProcessType === self::PAYMENT_TYPE_PLATFORM_ACCOUNT;
    }

    public function isProcessBatchPayments()
    {
        return $this->isCrmAdmin() && ($this->paymentProcessType === self::PAYMENT_TYPE_BATCH_PAYMENT);
    }


    /**
     * Calculate sum that company should pay today
     * @return array ['amount', 'fee', 'transactionIds']
     * amount - sum Amount
     * fee - sum fee
     * transactionIds - ids of transactions which has been participated in calculating totals
     * @return array
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function calculateCompanyLessonSumToPay(): array
    {
        $totals = [
            'amount' => 0,
            'fee' => 0,
            'transactionIds' => [],
        ];

        if (!$this->isProcessBatchPayments()) {
            return $totals;
        }

        //getting list of all clients of company
        $clientList = Account::find()->select('id')->column();
        if (empty($clientList)) {
            return $totals;
        }

        $query = Transaction::find()
            ->byStatus(Transaction::STATUS_NEW)
            ->child(null)
            ->lessonBatchPayment();

        //recalculate fee for lessons before calculating
        $lessonQuery = (clone($query))->with('lesson')->ofStudent($clientList);
        foreach ($lessonQuery->each(20) as $transaction) {
            $lesson = $transaction->lesson;
            if (empty($lesson)) {
                throw new Exception('Can not find lesson transaction id ' . $transaction->id);
            }
            $lesson->fee = $lesson->getAmount()['fee'];
            $transaction->fee = $lesson->fee;
            if (!$lesson->save(false) || !$transaction->save(false)) {
                $exceptionMessage = 'Failed to save calculated fee value lessonId = ' .
                    $lesson->id . ' transactionId = ' . $transaction->id;
                throw new Exception($exceptionMessage);
            }
        }

        //sum amount and fee for each company client
        foreach ($clientList as $clientId) {
            //looking for all new (approved) transactions for each client that doesn't have parent id
            $sumQuery = (clone $query)->ofStudent($clientId);

            $tableName = Transaction::tableName();
            $select = 'SUM(' . $tableName . '.amount) as sAmount, SUM(' . $tableName . '.fee) as sFee';
            $queryResult = $sumQuery->select(new Expression($select))
                ->createCommand()
                ->queryOne();

            $totals['amount'] += (double)($queryResult['sAmount'] ?? 0);
            $totals['fee'] += (double)($queryResult['sFee'] ?? 0);

            //save ids of transactions
            $transactionIds = $sumQuery->select('id')->column();
            $totals['transactionIds'] = array_merge($totals['transactionIds'], $transactionIds);
        }

        return $totals;
    }

    /**
     * Account for using in calculation processes (own for all roles except company admins and employees)
     * @return $this|Account
     */
    public function getProcessAccount()
    {
        return $this;
    }

    /**
     * @param $companyId
     * @return bool
     */
    public function isAdminOf($companyId): bool
    {
        return $this->id == $companyId
            || (
            \Yii::$app->authManager->checkAccess($this->id, Role::ROLE_CRM_ADMIN)
            );
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountTeam(): ActiveQuery
    {
        return $this->hasOne(static::$accountTeamClass, ['accountId' => 'id']);
    }

    /**
     * @return Account
     */
    public function selectNotificationReceiver(): self
    {
        if ($this->isPatient()) {
            /**
             * @var EmployeeClient $employeeClientRelation
             */
            $employeeClientRelation = EmployeeClient::find()
                ->andWhere(['clientId' => $this->id])
                ->joinWith('client')
                ->one();
            //if company client has related employee - send notification email to him
            $account = $employeeClientRelation->employee ?? null;
        }
        return $account ?? $this;
    }

    public static function getSEOTutorsQuery()
    {
        return Account::findWithoutRestrictions()
            ->tutor()
            ->andWhere(['searchHide' => 0])
            ->andWhere([
                'status' => [
                    Account::STATUS_ACTIVE,
                ],
            ])
            ->andWhere(
                [
                    '>=',
                    'profileUniqueWordsCount',
                    static::SEE_ALL_ACCOUNT_WORDS_LIMIT
                ]
            )
            ->joinWith('profile');
    }

    public function getTopTutorSubjectByLessonCount()
    {
        if (!$this->isTutor()) {
            return null;
        }

        $subjectId = $this->getTutorLessons()
            ->andWhere(['in', 'subjectId', ArrayHelper::getColumn($this->subjects, 'id')])
            ->select(['subjectId', 'COUNT(*) as count'])
            ->orderBy(['count' => SORT_DESC])
            ->groupBy(['subjectId'])
            ->scalar();

        if (empty($subjectId)) {
            $subjectId = $this->subjects[0]['subjectId'];
        }

        return $subjectId;
    }

    /**
     * @return bool
     */
    public function isHiddenFromIndexing(): bool
    {
        return $this->searchHide || $this->profileUniqueWordsCount < Account::SEE_ALL_ACCOUNT_WORDS_LIMIT;
    }

    public function routeNotificationForMail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function routeNotificationForPusher()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function routeNotificationForSms()
    {
        return $this->profile->phoneNumber;
    }

    /**
     * @return array
     */
    public function routeNotificationForDatabase()
    {
        return [self::class, $this->id];
    }

    /**
     * @return array|string[]
     */
    public function viaChannels()
    {
        return ['mail', 'sms', 'pusher', 'database'];
    }

    /**
     * @return ActiveQuery|\yii\db\ActiveQueryInterface|NotificationQuery
     */
    public function getNotifications()
    {
        return $this->hasMany(Notification::class, ['notifiable_id' => 'id'])
            ->andOnCondition(['notifiable_type' => self::class]);
    }
}

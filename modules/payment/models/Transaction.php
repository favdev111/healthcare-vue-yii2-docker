<?php

namespace modules\payment\models;

use common\components\ActiveRecord;
use common\components\app\ConsoleApplication;
use common\components\BusinessDaysCalculator;
use common\components\Shareasale;
use common\helpers\QueueHelper;
use common\models\PostPayment;
use modules\account\models\Account;
use modules\account\models\AccountClientStatistic;
use modules\account\models\AccountWithDeleted;
use modules\account\models\ClientBalanceTransaction;
use modules\notification\helpers\NotificationHelper;
use modules\payment\components\TransferHandlerService;
use Stripe\BalanceTransaction;
use UrbanIndo\Yii2\Queue\Job;
use modules\account\models\Lesson;
use modules\account\models\Account as UserAccount;
use modules\notification\models\Notification;
use modules\payment\models\query\DeclineChargeQuery;
use modules\payment\models\query\TransactionQuery;
use modules\payment\Module;
use Yii;
use yii\base\Exception;
use yii\bootstrap\Html;
use yii\helpers\Console;

/**
 * This is the model class for table "{{%transaction}}".
 *
 * @property integer $id
 * @property string $transactionExternalId
 * @property string $parentId
 * @property string $response
 * @property integer $status
 * @property integer $type
 * @property integer $refundInitiator
 * @property double $amount
 * @property double $fee
 * @property string $processDate
 * @property string $createdAt
 * @property string $description
 * @property integer $studentId
 * @property integer $tutorId
 * @property integer $objectType
 * @property integer $objectId
 * @property integer $bankTransactionId
 * @property integer $isNeedApprove
 * @property integer $isWaitingForApprove
 * @property integer $amountWithFee
 * @property integer $billingCycleStatus
 * @property integer $groupTransactionId
 * @property boolean $useMainPlatformPaymentProcess
 *
 * @property Lesson $lesson
 * @property Account $student
 * @property Account $tutor
 * @property Transaction $parent
 * @property Transaction $company - company related to Group Charge Transaction
 * @property Transaction[] $transfers - list of transactions with object type TYPE_LESSON_BATCH_PAYMENT
 * related to transaction  with object type TYPE_COMPANY_GROUP_PAYMENT
 * @property Transaction $groupTransaction - groupCharge related to lesson transfer
 * @property ClientBalanceTransaction $relatedClientBalance
 * @property string $responseString
 */
class Transaction extends ActiveRecord
{
    const STRIPE_CAPTURE = 1;
    const STRIPE_CHARGE = 2;
    const STRIPE_REFUND = 3;
    const STRIPE_TRANSFER = 4;
    const PARTIAL_REFUND = 5;

    /**
     * TODO: STATUS_NEW deprecated?
     */
    const STATUS_NEW = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = 2;
    const STATUS_WAITING_FOR_APPROVE = 3;
    const STATUS_REJECTED = 4;
    const STATUS_PENDING = 5;

    const NEW_BILLING_CYCLE_STATUS = 1;
    const OLD_BILLING_CYCLE_STATUS = 0;

    const TYPE_ACCOUNT = 1;
    const TYPE_LESSON = 2;
    const TYPE_BACKGROUNDCHECKREPORT = 3;
    const TYPE_CLIENT_BALANCE_AUTO = 4;
    const TYPE_CLIENT_BALANCE_MANUAL_CHARGE = 5;
    const TYPE_CLIENT_BALANCE_POST_PAYMENT = 6;
    /**
     * do not process transaction with TYPE_LESSON_BATCH_PAYMENT object type in queue
     */
    const TYPE_LESSON_BATCH_PAYMENT = 7;

    /**
     * Group transaction
     */
    const TYPE_COMPANY_GROUP_PAYMENT = 8;

    public $checkApprove = true;

    public static function clientBalanceTypes()
    {
        return [
            self::TYPE_CLIENT_BALANCE_AUTO,
            self::TYPE_CLIENT_BALANCE_MANUAL_CHARGE,
            self::TYPE_CLIENT_BALANCE_POST_PAYMENT,
        ];
    }

    public static function lessonTypes()
    {
        return [
            self::TYPE_LESSON,
            self::TYPE_LESSON_BATCH_PAYMENT,
        ];
    }

    const MARK_REFUND = 1;

    const MIN_NEEDS_APPROVE_SUM = 200;

    const CC_CHARGE_BANK_PROCESSING_DAYS = 3;
    const BANK_ACCOUNT_CHARGE_BANK_PROCESSING_DAYS = 8;

    const BALANCE_STATUS_AVAILABLE = 'available';

    protected $isCompanyBatchPayments;

    public $statuses = [
        self::STATUS_NEW => 'New',
        self::STATUS_SUCCESS => 'Success',
        self::STATUS_ERROR => 'Error',
        self::STATUS_WAITING_FOR_APPROVE => 'Waiting for approve',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_PENDING => 'Pending',
    ];

    public static $typesObject = [
        self::TYPE_ACCOUNT => 'Account',
        self::TYPE_LESSON => 'Lesson',
        self::TYPE_BACKGROUNDCHECKREPORT => 'Background check report',
        self::TYPE_CLIENT_BALANCE_AUTO => 'Client balance',
        self::TYPE_CLIENT_BALANCE_MANUAL_CHARGE => 'Manually',
        self::TYPE_CLIENT_BALANCE_POST_PAYMENT => 'Post Payment',
        self::TYPE_LESSON_BATCH_PAYMENT => 'Lesson (batch Payment)',
        self::TYPE_COMPANY_GROUP_PAYMENT => 'Tutor Payouts',
    ];

    public function getTypeObjectLabel()
    {
        return static::$typesObject[$this->objectType];
    }

    public function getTypes()
    {
        return static::$types;
    }

    public static $types = [
        self::STRIPE_CAPTURE => 'Capture',
        self::STRIPE_CHARGE => 'Charge',
        self::STRIPE_REFUND => 'Refund',
        self::STRIPE_TRANSFER => 'Transfer',
        self::PARTIAL_REFUND => 'Partial refund',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%transaction}}';
    }

    /**
     * @param UserAccount $account
     * @return array|null|static
     */
    public static function getLastUnapproved(UserAccount $account)
    {
        return static::find()
            ->andWhere(['status' => static::STATUS_WAITING_FOR_APPROVE])
            ->andWhere(['tutorId' => $account->id])
            ->orderBy(['createdAt' => SORT_DESC])
            ->one();
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        ];
    }

    /**
     * Is transaction need approve
     *
     * @return bool
     */
    public function getIsNeedApprove()
    {
        return $this->checkApprove && ($this->isLesson() || $this->isLessonBatchPayment()) && (($this->amount + $this->fee) >= static::MIN_NEEDS_APPROVE_SUM);
    }

    /**
     * Setting status for new transaction. Depends on sum of payment
     */
    public function setNewRecordStatus()
    {
        if (($this->isNewRecord) && ($this->status === null)) {
            ($this->isNeedApprove) ? $this->setStatusWaiting() : $this->setStatusNew();
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function setStatusNew()
    {
        $this->status = static::STATUS_NEW;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isStatusError()
    {
        return $this->status === static::STATUS_ERROR;
    }

    /**
     * @return $this
     */
    public function setStatusWaiting()
    {
        $this->status = static::STATUS_WAITING_FOR_APPROVE;
        return $this;
    }

    /**
     * @return bool
     */
    public function isStatusPending()
    {
        return $this->status === static::STATUS_PENDING;
    }

    /**
     * @return $this
     */
    public function setStatusRejected()
    {
        $this->status = static::STATUS_REJECTED;
        return $this;
    }

    public function isStatusRejected()
    {
        return $this->status === static::STATUS_REJECTED;
    }

    public function isTypeError()
    {
        return $this->status === static::STATUS_ERROR;
    }

    public function isPartialRefundOfGroupTransaction()
    {
        return $this->isTypePartialRefund() && !empty($this->parent) && $this->parent->isGroupChargeTransaction();
    }

    /**
     * @return boolean
     */
    public function updateStatusRejected()
    {
        $this->setStatusRejected();
        if (!$this->save(false)) {
            return false;
        }

        return true;
    }

    /**
     * @return TransactionQuery
     * @inheritdoc
     */
    public static function find()
    {
        return new TransactionQuery(static::class);
    }

    /**
     * Has transaction external id
     * @return bool
     */
    public function hasExternalId()
    {
        return ($this->transactionExternalId !== null);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lessonId' => 'Lesson ID',
            'transactionId' => 'Transaction ID',
            'parentTransactionId' => 'Parent Transaction ID',
            'response' => 'Response',
            'status' => 'Status',
            'type' => 'Type',
            'amount' => 'Amount',
            'fee' => 'Fee',
            'processDate' => 'Process Date',
            'createdAt' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLesson()
    {
        return $this->hasOne(Lesson::class, ['id' => 'objectId']);
    }

    public function getPostPayment()
    {
        return $this->hasOne(PostPayment::class, ['id' => 'objectId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(AccountWithDeleted::class, ['id' => 'objectId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRefundInitiator()
    {
        return $this->hasOne(\backend\models\Account::class, ['id' => 'refundInitiator']);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->setNewRecordStatus();
        $this->useMainPlatformPaymentProcess = true;
        return parent::beforeSave($insert);
    }

    /**
     * @return bool
     */
    public function getIsWaitingForApprove()
    {
        return static::STATUS_WAITING_FOR_APPROVE === $this->status;
    }

    public function getCharge()
    {
        if ($this->objectType == self::TYPE_LESSON) {
            $trCharge = self::find()
                ->andWhere(['objectType' => self::TYPE_LESSON])
                ->andWhere(['status' => self::STATUS_SUCCESS])
                ->andWhere(['objectId' => $this->objectId])
                ->andWhere(['in', 'type',  [ self::STRIPE_CHARGE, self::STRIPE_REFUND ]])
                ->one();
            if ($trCharge) {
                return $trCharge;
            }
        }

        return false;
    }


    /**
     * @return bool
     */
    public function allowRefund()
    {
        /**
         * For old billing cycle
         */
        if ($this->billingCycleStatus === self::OLD_BILLING_CYCLE_STATUS) {
            return time() < strtotime($this->processDate . ' +3 days');
        }

        return $this->isClientBalance() ? (bool)$this->calculateNotRefundedSum() : $this->status === self::STATUS_SUCCESS;
    }

    public function isLesson()
    {
        return ($this->objectType == self::TYPE_LESSON);
    }

    /**
     * is lesson transaction witch processing in group payment
     * @return bool
     */
    public function isLessonBatchPayment()
    {
        return $this->objectType === static::TYPE_LESSON_BATCH_PAYMENT;
    }

    public function isClientBalance()
    {
        return ($this->objectType == self::TYPE_CLIENT_BALANCE_AUTO || $this->objectType == self::TYPE_CLIENT_BALANCE_MANUAL_CHARGE || $this->objectType === self::TYPE_CLIENT_BALANCE_POST_PAYMENT);
    }

    public function isClientBalanceManualCharge()
    {
        return $this->objectType == self::TYPE_CLIENT_BALANCE_MANUAL_CHARGE;
    }

    public function selectClientBalanceTransactionType()
    {
        $type = null;
        switch ($this->objectType) {
            case static::TYPE_CLIENT_BALANCE_MANUAL_CHARGE:
                $type = ClientBalanceTransaction::TYPE_TRANSACTION_MANUAL;
                break;
            case static::TYPE_CLIENT_BALANCE_AUTO:
                $type = ClientBalanceTransaction::TYPE_TRANSACTION_AUTO;
                break;
            case static::TYPE_CLIENT_BALANCE_POST_PAYMENT:
                $type = ClientBalanceTransaction::TYPE_TRANSACTION_POST_PAYMENT;
                break;
        }
        return $type;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBalance()
    {
        return $this->hasOne(TransactionBalance::class, ['transactionId' => 'id']);
    }

    public static function getBalanceAmount($studentId)
    {
        $debit = self::find()->joinWith(['lesson' => function ($query) use ($studentId) {
            $query->andWhere(['lesson.studentId' => $studentId]);
        }
        ])->where([
            'and',
            ['=', 'transaction.status', self::STATUS_SUCCESS],
            ['=', 'transaction.type', self::STRIPE_CAPTURE],
        ])->sum('transaction.amount+transaction.fee');

        $credit = self::find()->joinWith(['lesson' => function ($query) use ($studentId) {
            $query->andWhere(['lesson.studentId' => $studentId]);
        }
        ])->where([
            'and',
            ['=', 'transaction.status', self::STATUS_SUCCESS],
            ['in', 'transaction.type', [self::STRIPE_CHARGE, self::STRIPE_REFUND]],
        ])->sum('transaction.amount+transaction.fee');

        $balance = $debit - $credit;
        return ['balance' => $balance, 'credit' => $credit, 'debit' => $debit];
    }

    /**
     * @return bool
     */
    public function isStatusSuccess()
    {
        return $this->status === self::STATUS_SUCCESS;
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
     * recursive
     * @return $this
     */
    public function getRootParent()
    {
        if ($this->hasParent()) {
            return $this->parent->getRootParent();
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function hasParent()
    {
        return (bool)$this->parent;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Transaction::class, ['id' => 'parentId']);
    }

    /**
     * @return DeclineChargeQuery
     */
    public function getDeclineCharges()
    {
        return $this->hasMany(DeclineCharge::class, ['chargeId' => 'id']);
    }

    public function getStatusText()
    {
        return $this->statuses[$this->status] ?? $this->statuses[$this->status] ?? $this->status;
    }

    public function getTypeText()
    {
        return $this->types[$this->type] ?? $this->types[$this->type] ?? $this->type;
    }

    /**
     * @return float
     */
    public function getAmountWithFee()
    {
        $total = $this->amount + $this->fee;
        return Yii::$app->formatter->asDecimal($total, 2);
    }

    public function getLast4()
    {
        return $this->response['source']['last4'] ?? null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransfers()
    {
        return $this->hasMany(static::class, ['groupTransactionId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroupTransaction()
    {
        return $this->hasOne(static::class, ['id' => 'groupTransactionId']);
    }


    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {

        parent::afterSave($insert, $changedAttributes);

        if ($insert && $this->isNeedApprove) {
            /**
             * @var Module $module
             */
            $module = Yii::$app->getModule('payment');
            $module->eventUnapprovedTransactionAppend($this);
        }

        if (
            (
                $insert
                || (
                    !$insert
                    && isset($changedAttributes['status'])
                )
            )
            && $this->status === self::STATUS_SUCCESS
            && $this->type === self::STRIPE_CHARGE
            && $this->objectType === self::TYPE_LESSON
            // Only first successful transaction is sent to ShareASale
            && !self::find()
                ->andWhere(['status' => self::STATUS_SUCCESS])
                ->andWhere(['type' => self::STRIPE_CHARGE])
                ->andWhere(['objectType' => self::TYPE_LESSON])
                ->andWhere(['studentId' => $this->studentId])
                ->andWhere(['not', ['id' => $this->id]])
                ->exists()
        ) {
            // Send request only if current transaction is Lesson which was paid (inserted as paid, or changed to paid)
            /**
             * @var $shareSale Shareasale
             */
            $shareSale = Yii::$app->shareasale;
            $shareSale->sendReference($this->studentId, $this->student->createdAt, $this->getAmountWithFee(), $this->id);
        }

        if (
            (

                (
                    // When status changed from Pending Approval -> New
                    !$insert
                    && isset($changedAttributes['status'])
                    && $changedAttributes['status'] = self::STATUS_WAITING_FOR_APPROVE
                    && $this->status === self::STATUS_NEW
                )
                || (
                    // Or when this is a new transaction with status New or BA transaction with status Pending
                    $insert
                    && (
                        $this->status === self::STATUS_NEW
                        || (
                            $this->status === self::STATUS_PENDING
                            && $this->isBankTransaction()
                        )
                    )
                )
            )
            // Only lesson charge transactions trigger "New transaction" event
            && ($this->objectType === self::TYPE_LESSON || $this->objectType === self::TYPE_LESSON_BATCH_PAYMENT)
            && ($this->type == self::STRIPE_CHARGE || $this->isTransfer())
            // And all transactions that were not declined today (e.g. NOT New transaction just after Error transaction)
            && !DeclineCharge::isDeclinedToday($this->getRootParent())
        ) {
            // All new transactions for lesson charge that were not yet declined
            /**
             * @var Module $paymentModule
             */
            $paymentModule = Yii::$app->getModule('payment');
            $paymentModule->eventNewTransaction($this);
        }

        //when transaction status changed to success - update tutor statistic
        if (
            isset($changedAttributes['status'])
            && $this->status === self::STATUS_SUCCESS
            && ($this->type === self::STRIPE_CHARGE || $this->type === self::STRIPE_TRANSFER)
            && in_array($this->objectType, Transaction::lessonTypes())
        ) {
            $statistic = AccountClientStatistic::getUserStatistic($this->lesson->tutorId);
            if ($statistic) {
                $statistic->totalEarned += $this->lesson->amount;
                $statistic->save(false);
            }
        }
    }

    /**
     * @return false|int
     * TODO find a way to determine the type of transaction (from credit card or from bank account)
     */
    public function isBankTransaction()
    {
        return  preg_match('/^py/', $this->transactionExternalId);
    }


    public function isHasChildTransactions()
    {
        return $childTransactionsExist = Transaction::find()
            ->child($this->id)
            ->exists();
    }

    public function isGroupChargeTransaction()
    {
        return $this->objectType === static::TYPE_COMPANY_GROUP_PAYMENT;
    }

    public function isAllowedReCharge()
    {
        if (empty($this->student)) {
            return false;
        }
        $cardOrBankAccount = $this->student->paymentCustomer ? $this->student->paymentCustomer->getActiveCardOrBankAccount() : null;
        //for main platform payment flow lessons do not check client card
        $checkCard = $this->isLessonTransfer() ? true : !empty($cardOrBankAccount);
        return $this->status === static::STATUS_ERROR
            && !$this->isHasChildTransactions()
            && (
                $this->isClientBalance()
                || ($this->isLessonBatchPayment() && $this->isStatusError())
                || ($this->isLessonTransfer() && $this->isStatusError())
            )
            && $checkCard;
    }

    public function isTransfer(): bool
    {
        return $this->type == self::STRIPE_TRANSFER;
    }

    /**
     * Lesson Transfer which was create when company has paymentProcessType = PAYMENT_TYPE_PLATFORM_ACCOUNT
     * @return bool
     */
    public function isLessonTransfer(): bool
    {
        return $this->isLesson() && $this->isTransfer();
    }

    public static function reCharge(Transaction $transaction)
    {
        if (!$transaction->isAllowedReCharge()) {
            return false;
        }

        //process error lessons batch payment
        if (
            ($transaction->isLessonBatchPayment() && $transaction->isStatusError())
        ) {
            $groupTransaction = $transaction->groupTransaction;
            if (empty($groupTransaction) && $transaction->isLessonBatchPayment()) {
                Yii::error("Can not recharge lesson batch payment with id {$transaction->id} reason: group transaction not found");
                return false;
            }

            $transferHandler = new TransferHandlerService();
            $transferHandler->processLessonTransfer($transaction, $groupTransaction->transactionExternalId ?? null);
            $task = new Job([
                'route' => 'payment/check-lessons-transfers-status',
                'data' => [
                    'tutorId' => $transaction->tutorId,
                ]
            ]);
            Yii::$app->queue->post($task);
            return $transaction->isStatusError() ? false : $transaction;
        }

        $newTransaction = new Transaction([
            'studentId' => $transaction->studentId,
            'tutorId' => $transaction->tutorId,
            'objectId' => $transaction->objectId,
            'objectType' => $transaction->objectType,
            'fee' => $transaction->fee,
            'status' => Transaction::STATUS_NEW,
            'amount' => $transaction->amount,
            'type' => $transaction->type,
            'processDate' => date('Y-m-d'),
            /*set last error-transaction as parent*/
            'parentId' => $transaction->id
        ]);

        if ($newTransaction->isLessonTransfer()) {
            $newTransaction->checkApprove = false;
        }

        if ($newTransaction->save(false) && $transaction->isClientBalance()) {
            QueueHelper::processCharge($newTransaction);
        }

        return $newTransaction;
    }

    public function unPinRelatedDeclineNotification()
    {
        Notification::unPinRelatedNotifications(NotificationHelper::OBJECT_TYPE_STUDENT_ACCOUNT, $this->studentId, NotificationHelper::TYPE_AUTORENEW_DECLINES);
    }

    public static function amountToDollars($amountInCents)
    {
        return $amountInCents / 100;
    }

    public static function amountToCents($amountInDollars)
    {
        return $amountInDollars * 100;
    }

    public function isAllowedRefund()
    {
        $usualTransactions = ($this->isClientBalance() && $this->calculateNotRefundedSum()) || $this->isLesson() || $this->isLessonBatchPayment() || $this->isGroupChargeTransaction();
        $chargeWithStatusSuccess = $this->type === static::STRIPE_CHARGE && in_array($this->status, [Transaction::STATUS_SUCCESS]);
        $captureWithStatusSuccessOrNew = $this->type === static::STRIPE_CAPTURE && in_array($this->status, [Transaction::STATUS_SUCCESS, Transaction::STATUS_NEW]);
        $successLessonTransfer = $this->isLessonTransfer() && $this->isStatusSuccess();
        $isUsualTransactionAllowedToRefund = $usualTransactions && ($chargeWithStatusSuccess || $captureWithStatusSuccessOrNew);

        return $isUsualTransactionAllowedToRefund || $successLessonTransfer;
    }

    /**
     * @return bool
     */
    public function isNeedShowRefundBlockOrButton()
    {
         return $this->isAllowedRefund() && !$this->isGroupChargeTransaction();
    }

    public function isTypePartialRefund()
    {
        return $this->type === static::PARTIAL_REFUND;
    }

    /**
     * @return bool
     */
    public function isTypeRefund()
    {
        return $this->type === static::STRIPE_REFUND;
    }

    public function getTotalPartialRefundSum()
    {
        return (double)Transaction::find()->partialRefundsOf($this->id)->sum('amount');
    }

    public function isHasPartialRefunds()
    {
        //transaction with error status can not have partial refunds
        if ($this->isStatusError()) {
            return false;
        }
        return Transaction::find()->partialRefundsOf($this->id)->exists();
    }

    public function calculateNotRefundedSum()
    {
        return $this->amount - $this->getTotalPartialRefundSum();
    }

    public static function logTransactionError(Transaction $transaction, \Throwable $ex, $type)
    {
        $text = 'Transaction ' . $transaction->id . "($type) - " . $ex->getMessage() . "\n" . json_encode($ex->getTrace());
        Yii::error($text, 'payment');
    }

    public static function calcExpectedPayoutDate($student, $dateTime)
    {
        if (!$dateTime) {
            return null;
        }

        if (
            $student->isCompanyClient()
            && $student->companyWithoutRestrictions->isPaymentMethodBankAccount()
            && !$student->companyWithoutRestrictions->isPaymentTypePlatformAccount()
        ) {
            $howManyDays = static::BANK_ACCOUNT_CHARGE_BANK_PROCESSING_DAYS;
        } else {
            $howManyDays = static::CC_CHARGE_BANK_PROCESSING_DAYS;
        }

        $calculator = new BusinessDaysCalculator(
            $dateTime,
            BusinessDaysCalculator::getHolidays(),
            [
                BusinessDaysCalculator::SATURDAY,
                BusinessDaysCalculator::SUNDAY
            ]
        );
        return $calculator
            ->addBusinessDays($howManyDays)
            ->getDate()
            ->format("m/d/y");
    }

    public function isPostPaymentTransaction()
    {
        return $this->objectType === static::TYPE_CLIENT_BALANCE_POST_PAYMENT;
    }

    /**
     * get "balance" field for lesson table (lesson tab in student cabinet)
     * @return float|int|null
     */
    public function getLessonBalance()
    {
        return  Yii::$app->user->isPatient() ? number_format($this->lesson->getClientPrice(), 2) : $this->getAmountWithFee();
    }

    /**
     * check student company for batch payment option
     * @return bool
     * @throws Exception
     */
    public function getIsCompanyBatchPayments()
    {
        if (empty($this->studentId)) {
            throw new Exception('StudentId must be set.');
        }

        if (empty($this->isCompanyBatchPayments)) {
            $this->isCompanyBatchPayments = Account::find()
                ->select('c.paymentProcessType')
                ->joinWith('company as c')
                ->andWhere([Account::tableName() . '.id' => $this->studentId])
                ->andWhere(['c.paymentProcessType' => Account::PAYMENT_TYPE_BATCH_PAYMENT])
                ->exists();
        }
        return $this->isCompanyBatchPayments;
    }

    /**
     * check client company for ability to process batch payments
     * select appropriate object type
     * @return int
     */
    public function selectLessonTransactionObjectType()
    {
        return $this->getIsCompanyBatchPayments() ? static::TYPE_LESSON_BATCH_PAYMENT : static::TYPE_LESSON;
    }

    /**
     * get balance transaction related to transfer and check it status
     * return true - if status is available
     * @return bool
     * @throws Exception
     */
    public function isFundsAvailable()
    {
        if (!$this->isLessonBatchPayment() && !($this->isLessonTransfer())) {
            throw new Exception('Unsupported transaction type. Expected:TYPE_LESSON_BATCH_PAYMENT or Lesson transfer');
        }
        $balanceTransactionId = $this->response['balance_transaction'] ?? null;
        if (empty($balanceTransactionId)) {
            $balanceTransactionId = $this->response['newResponse']['balance_transaction'];
        }
        if (empty($balanceTransactionId)) {
            Yii::warning('Balance transaction id has not been set.', 'payment');
            return false;
        }

        \Stripe\Stripe::setApiKey(Yii::$app->payment->privateKey);
        $response  = BalanceTransaction::retrieve($balanceTransactionId);

        $status = $response->status ?? null;
        if (Yii::$app instanceof ConsoleApplication) {
            Console::output('Response :' . json_encode($response));
            Console::output('Current status = ' . $status);
        }
        return static::BALANCE_STATUS_AVAILABLE === $status;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRelatedClientBalance()
    {
        return $this->hasOne(ClientBalanceTransaction::class, ['transactionId' => 'id']);
    }


    /**
     * @return null|string
     */
    public function getResponseString(): string
    {
        $response = $this->response ?? null;

        if (!empty($response)) {
            //if string is in response
            if (is_string($response)) {
                $response = str_replace('"', '', $response);
                return str_replace("\\", '', $response);
            } else {
                if (!empty($response['newResponse'])) {
                    return $response['newResponse']['failure_message'];
                } else {
                    return $response['failure_message'];
                }
            }
        }
        return '';
    }
}

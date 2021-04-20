<?php

namespace modules\payment\components;

use common\components\app\ConsoleApplication;
use common\helpers\EmailHelper;
use modules\account\models\Lesson;
use modules\account\models\PaymentAccount;
use modules\notification\helpers\NotificationHelper;
use modules\notification\models\Notification;
use modules\payment\components\interfaces\ChargePaymentInterface;
use modules\payment\models\DeclineCharge;
use modules\payment\models\interfaces\PaymentSourceInterface;
use Stripe\Exception\CardException;
use Yii;
use modules\payment\models\TransactionBalance;
use yii\helpers\Console;
use modules\account\models\Account;
use modules\payment\models\Transaction;
use yii\helpers\Json;
use yii\mutex\MysqlMutex;

/**
 * Use only on console context
 * Class ChargeHandlerService
 * @package modules\payment\components
 * @todo: Move general logic to AbstractHandlerService
 */
class ChargeHandlerService extends AbstractHandlerService
{
    const MAX_CARD_ERROR_NOTIFICATION_COUNT = 2;
    const NO_CARD_ERROR_NOTIFICATION_COUNT = 0;

    const PERCENT_100 = 100;

    const TRANSACTION_STATUS_SUCCEEDED = 'succeeded';
    const TRANSACTION_STATUS_PENDING = 'pending';

    /**
     * @var Transaction[] $transactions
     */
    protected $transactions = [];


    /**
     * ChargeHandler constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->setChargePayment(Yii::$app->payment);
    }

    /**
     * Run processing charge transactions
     */
    public function run()
    {
        $this->ensureTransactions();


        $this->sendStdout("Payment list:\n");

        return ($this->transactions) ? $this->processTransactions() : false;
    }

    /**
     * Setter for charge payment component
     * @param ChargePaymentInterface $payment
     * @return $this
     */
    public function setChargePayment(ChargePaymentInterface $payment)
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * Getter for charge payment component
     * @return ChargePaymentInterface $payment
     */
    public function getChargePayment()
    {
        return $this->payment;
    }

    /**
     * Ensure transactions
     */
    protected function ensureTransactions()
    {
        $this->transactions = Transaction::find()->byNewTransactions()->all();
    }

    /**
     * Get destination
     * @param integer $accountId
     * @return PaymentAccount | null
     */
    private function getDestination($accountId)
    {
        return PaymentAccount::findOne(['accountId' => $accountId]);
    }

    /**
     * Process transactions
     */
    private function processTransactions()
    {
        foreach ($this->transactions as $transaction) {
            $this->processTransaction($transaction);
        }
    }

    /**
     * @param $transaction Transaction
     * @param $forceProcessToday boolean
     * @return boolean
     */
    public function processTransaction($transaction, $forceProcessToday = false)
    {
        Yii::info('Trying to process charge: ' . $transaction->id);
        $mutexName = 'Charge_process_transaction_' . $transaction->id;
        $mutex = new MysqlMutex();
        if ($mutex->acquire($mutexName)) {
            try {
                switch ($transaction->objectType) {
                    case Transaction::TYPE_LESSON:
                        $lesson = $transaction->lesson;
                        $this->sendStdout("Processing Lesson Charge {$lesson->id}\n");
                        $destination = $this->getDestination($lesson->tutorId);
                        $accountId = $lesson->tutorId;
                        break;

                    case Transaction::TYPE_COMPANY_GROUP_PAYMENT:
                        $response = Yii::$app->payment->chargeToPlatformAccount(
                            true,
                            $transaction->company->paymentCustomer->customerId,
                            Transaction::amountToCents($transaction->amount),
                            'Tutor Payouts ' . date('Y-m-d')
                        );
                        $transaction->transactionExternalId = $response->id;
                        $transaction->response = $response;
                        //success status should be set after processing in webhook handler
                        $transaction->status = Transaction::STATUS_PENDING ;
                        $transaction->save(false);
                        if (Yii::$app instanceof ConsoleApplication) {
                            Console::output('Process success: external id:' . $transaction->transactionExternalId);
                        }
                        return true;
                        break;

                    case Transaction::TYPE_CLIENT_BALANCE_AUTO:
                    case Transaction::TYPE_CLIENT_BALANCE_MANUAL_CHARGE:
                    case Transaction::TYPE_CLIENT_BALANCE_POST_PAYMENT:
                        $client = $transaction->student;
                        break;

                    default:
                        return false;
                        break;
                }

                if (!$forceProcessToday && DeclineCharge::isDeclinedToday($transaction->getRootParent())) {
                    return false;
                }
                if (!$destination || !$destination->paymentAccountId) {
                    Yii::error(
                        'Transaction ' . $transaction->id . ' - payment destination is not found. Account: ' . $accountId,
                        'payment'
                    );
                    $this->sendStdout("payment destination is not found\n");
                    EmailHelper::sendMessageToAdmin(
                        'Payment error',
                        'Transaction ' . $transaction->id . ' - payment destination is not found'
                    );
                    return false;
                }
                $this->handleCharge($transaction, $destination);
                DeclineCharge::releaseDeclinedTransaction($transaction->student);
                $complete = true;
            } catch (CardException $e) {
                Yii::error('Failed process charge. Exception: ' . $e->getMessage(), 'charge');
                $this->processCardError($e, $transaction);
            } catch (\Exception $e) {
                $this->exceptionDefaultLog($e, $transaction);
                $this->saveTransaction(
                    $transaction,
                    [
                        'status' => Transaction::STATUS_ERROR,
                        'response' => Json::encode($e->getMessage()),
                    ]
                );
            }
            return !empty($complete) ? true : false;
        } else {
            Yii::error('Transaction is blocked to update.', 'charge');
            return false;
        }
    }

    /**
     * @param Transaction $charge
     */
    protected static function declineChargeNotify(Transaction $charge)
    {
        $moduleTransaction = Yii::$app->getModule('payment');
        $moduleTransaction->eventCardDecline($charge);
    }

    /**
     * @param Transaction $charge
     */
    public static function processDeclineCharges(Transaction $charge)
    {
        $student = $charge->student;
        $student->refresh();

        /**
         * If student was baned on previous iterations
         */
        if ($student->isActive() === false) {
            return;
        }

        DeclineCharge::createNew($charge);

        /**
         * Declines other charges of this charge student for this charge tutor
         */
        if (DeclineCharge::isNotifiedToday($charge)) {
            return;
        }

        self::declineChargeNotify($charge);
    }

    /**
     * Process card error \Stripe\Error\Card Exception
     *
     * @param CardException $e
     * @param Transaction $transaction
     */
    private function processCardError(CardException $e, Transaction $transaction)
    {
        $this->exceptionDefaultLog($e, $transaction);

        // Since it's a decline, CardException will be caught
        /**
         * @var $error \Stripe\ErrorObject
         */
        $err = $e->getError();
        $this
            ->sendStdout('Status is:' . $e->getHttpStatus() . "\n")
            ->sendStdout('Type is:' . ($err->type ?? '') . "\n")
            ->sendStdout('Code is:' . ($err->code ?? '') . "\n")
            ->sendStdout('Message is:' . ($err->message ?? '') . "\n");

        /**
         * Save old transaction changes
         */
        $this->saveTransaction(
            $transaction,
            [
                'status' => Transaction::STATUS_ERROR,
                'response' => json_encode($err ?? null),
                'transactionExternalId' => $err->charge ?? null,
            ]
        );

        //do not create client notifications (Group charge doesn't have related student or tutor)
        //and copy of transaction with status NEW for group payments (declined group charge will stay in ERROR status,related transfers will be handle by next group charge)
        if ($transaction->isGroupChargeTransaction()) {
            return;
        }

        self::processDeclineCharges($transaction);

        if ($transaction->isClientBalance()) {
            $notify = new Notification();
            if ($transaction->isPostPaymentTransaction()) {
                $notify->type = NotificationHelper::TYPE_POST_PAYMENT_TRANSACTION_DECLINES;
            } else {
                $notify->type = NotificationHelper::TYPE_AUTORENEW_DECLINES;
            }

            $extraData = [
                'transactionId' => $transaction->id,
                'transactionCreatedAt' => $transaction->createdAt,
                'studentId' => $transaction->studentId,
            ];

            $notify->accountId = $transaction->studentId;
            $notify->initiatorId = $transaction->tutorId;

            //fir post payment transaction objectId contains Id of PostPayment
            if ($transaction->isPostPaymentTransaction()) {
                $objType = NotificationHelper::OBJECT_TYPE_POST_PAYMENT;
                $objId = $transaction->objectId;
                $extraData = array_merge($extraData, ['postPaymentId' => $transaction->objectId]);
            } else {
                /*unpin Success charge notification*/
                $type = NotificationHelper::TYPE__STUDENT__CHARGE_SUCCESS;
                $objType = NotificationHelper::OBJECT_TYPE_STUDENT_ACCOUNT;
                $objId = $transaction->studentId;
                Notification::unPinRelatedNotifications($objType, $transaction->studentId, $type);
            }

            $notify->extraData = $extraData;

            /*pin autorenew decline notification*/
            $notify->pin($objType, $objId);
            $notify->save(false);
        } else {
            /**
             * Create new transaction
             */
            $newTransaction = $this->saveTransaction(
                Yii::createObject(Transaction::class),
                [
                    'studentId' => $transaction->studentId,
                    'tutorId' => $transaction->tutorId,
                    'status' => Transaction::STATUS_NEW,
                    'parentId' => $transaction->id,
                    'processDate' => $transaction->processDate,
                    'fee' => $transaction->fee,
                    'amount' => $transaction->amount,
                    'type' => $transaction->type,
                    'objectId' => $transaction->objectId,
                    'objectType' => $transaction->objectType,
                    'billingCycleStatus' => Transaction::NEW_BILLING_CYCLE_STATUS,
                ],
                false
            );
        }
    }

    /**
     * Process handle
     *
     * @param Transaction $transaction
     * @param PaymentAccount $destination
     */
    private function handleCharge(Transaction $transaction, PaymentAccount $destination)
    {
        if ($transaction->hasExternalId()) {
            // This was used for old payment method. No more such transactions should be processed here
            Yii::error('Transaction already has external id', 'charge');
            EmailHelper::sendMessageToAdmin(
                'Payment error',
                'Transaction ' . $transaction->id . ' - tried to process transaction with external ID.'
            );
            return;
        }
        $isNewBillingCycle = true;
        /**
         * For new payment method
         */
        $newTransaction = $this->handleTransaction(
            $transaction,
            $destination,
            Transaction::NEW_BILLING_CYCLE_STATUS
        );

        if (!$newTransaction) {
            return;
        }

        if ($transaction->status !== Transaction::STATUS_SUCCESS) {
            // No need to proceed for non success transactions.
            return;
        }

        if ($transaction->isLesson()) {
            $lesson = $transaction->lesson;
            $lesson->transactionComplete();
            $lesson->save(false);

            $balance = Transaction::getBalanceAmount($lesson->studentId);

            $trBalance = new TransactionBalance();
            $trBalance->transactionId = $transaction->id;
            /**
             * For different old and new billing cycle transactions  (credit for new, balance for old)
             */
            $trBalance->balance = $isNewBillingCycle ? $balance['credit'] : $balance['balance'];

            $trBalance->save(false);
        }

        $moduleTransaction = Yii::$app->getModule('payment');
        $moduleTransaction->eventChargeSuccess($transaction);
    }

    /**
     * @todo Need Refactoring. Output log context
     * @param Transaction $transaction
     * @param PaymentAccount $destination
     * @param integer $billingCycleStatus
     * @return mixed
     */
    private function handleTransaction(
        Transaction $transaction,
        PaymentAccount $destination,
        $billingCycleStatus = Transaction::OLD_BILLING_CYCLE_STATUS
    ) {
        /**
         * @var $student Account
         */
        $student = $transaction->student;

        $source = $student->paymentCustomer ? $student->paymentCustomer->getActiveCardOrBankAccount() : null;

        if (!$source) {
            $sourceType = $student->isPatient() ? 'card / bank account' : 'card';
            $this->log('Transaction ' . $transaction->id . ' - payment source ' . $sourceType . ' is not found. Student: ' . $student->id);
            $this->sendStdout(
                'Transaction ' . $transaction->id . ' - payment source ' . $sourceType . ' is not found' . "\n",
                Console::BOLD
            );
            return false;
        }

        $chargeStripeFeeOnConnectedAccount = false;
        if ($transaction->isLesson()) {
            $toName = $transaction->isLesson() ? $transaction->lesson->tutor->profile->showName : '';
            $amountInDollars = ($transaction->lesson->amount + $transaction->lesson->fee);
            $fee = ($transaction->lesson->fee) * static::PERCENT_100;
        } elseif ($transaction->isClientBalance()) {
            $toName = 'HeyTutor';
            $amountInDollars = $transaction->amount;
            $fee = 0;
            $chargeStripeFeeOnConnectedAccount = true;
        } else {
            return false;
        }

        $student->profile->showFullName = true;
        $description = 'Charge from ' . ($transaction->isClientBalance() ? $student->profile->fullName() : $student->profile->showName) . ' to ' . $toName . ' amount: ' . $amountInDollars;

        Yii::info('Trying to create charge to main platform.', 'charge');
        $stripeTransaction = $this->payment->chargeToPlatformAccount(
            true,
            $source->paymentCustomer->customerId,
            $amountInDollars * static::PERCENT_100,
            $description
        );
        Yii::info('Response: ' . json_encode($stripeTransaction), 'charge');

        if ($stripeTransaction->status == static::TRANSACTION_STATUS_SUCCEEDED) {
            /**
             * Save old transaction changes
             */
            $this->saveTransaction(
                $transaction,
                [
                    'status' => Transaction::STATUS_SUCCESS,
                    'billingCycleStatus' => $billingCycleStatus,
                    'response' => $stripeTransaction,
                    'transactionExternalId' => $stripeTransaction->id,
                ],
                false
            );

            /*unpin related decline notification*/
            if ($transaction->isClientBalance()) {
                if ($transaction->isPostPaymentTransaction()) {
                    $transaction->postPayment->unpinRelatedFailedChargeNotifications();
                } else {
                    $transaction->unPinRelatedDeclineNotification();
                }
            }

            /*if ($transaction->isLesson()) {
                $moduleTransaction = $this->application->getModule('payment');
                $moduleTransaction->eventLeaveReview($transaction->lesson);
            }*/

            return $transaction;
        }

        if ($stripeTransaction->status == static::TRANSACTION_STATUS_PENDING) {
            /**
             * Save old transaction changes
             */
            $this->saveTransaction(
                $transaction,
                [
                    'status' => Transaction::STATUS_PENDING,
                    'billingCycleStatus' => $billingCycleStatus,
                    'response' => $stripeTransaction,
                    'transactionExternalId' => $stripeTransaction->id,
                ],
                false
            );

            return $transaction;
        }

        return false;
    }

    /**
     * Default Log message on each exception
     * @param \Exception $e
     * @param Transaction $transaction
     */
    private function exceptionDefaultLog(\Exception $e, Transaction $transaction)
    {
        $text = 'Transaction ' . $transaction->id . ' - ' . $e->getMessage() . ' - ' . $e->getTraceAsString();
        $this->log($text);
    }

    /**
     * Log an error
     * @param $message
     * @todo: Move logger logic outside this Service
     */
    private function log($message)
    {
        Yii::error($message, 'charge');
        EmailHelper::sendMessageToAdmin('Payment error', $message);
    }
}

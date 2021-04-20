<?php

namespace modules\payment\components;

use common\components\Formatter;
use modules\account\models\Lesson;
use modules\account\models\PaymentAccount;
use modules\payment\models\PaymentAccountBalance;
use modules\payment\models\PayoutError;
use modules\payment\models\PlatformPayout;
use modules\payment\models\Transaction;
use modules\payment\Module;
use UrbanIndo\Yii2\Queue\Job;
use Yii;
use yii\base\BaseObject;
use yii\console\Exception;
use yii\helpers\Json;

class StripeWebhookHandler extends BaseObject
{
    public $event;
    public $encodedEvent;

    const EVENT_BALANCE_AVAILABLE = 'balance.available';
    const EVENT_CHARGE_FAILED = 'charge.failed';
    const EVENT_CHARGE_SUCCEEDED = 'charge.succeeded';
    //for partial refund of groupPayment
    const EVENT_CHARGE_REFUNDED = 'charge.refunded';

    const EVENT_PAYOUT_SUCCEEDED = 'payout.paid';
    const EVENT_PAYOUT_FAILED = 'payout.failed';

    const EVENT_ACCOUNT_UPDATED = 'account.updated';


    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->event = json_decode($this->encodedEvent, true);
    }

    public static function processableEvents()
    {
        return [
            self::EVENT_BALANCE_AVAILABLE,
            self::EVENT_CHARGE_FAILED,
            self::EVENT_CHARGE_SUCCEEDED,
            self::EVENT_CHARGE_REFUNDED,
            self::EVENT_PAYOUT_FAILED,
            self::EVENT_PAYOUT_SUCCEEDED,
            self::EVENT_ACCOUNT_UPDATED,
        ];
    }

    public function processEvent()
    {
        Yii::info('New Stripe Webhook received: ' . Json::encode($this->event), 'payment');

        if ($this->event['object'] != 'event' || !in_array($this->event['type'], self::processableEvents())) {
            Yii::error('Invalid event received: ' . Json::encode($this->event), ' payment');
            // Return true to prevent Stripe from sending this request again
            return true;
        }

        if (!$this->checkStripeSignature($this->encodedEvent)) {
            Yii::error('Invalid stripe signature received: ' . Json::encode($this->event), 'payment');
            // Return true to prevent Stripe from sending this request again
            return true;
        }

        switch ($this->event['type']) {
            case self::EVENT_BALANCE_AVAILABLE:
                if (empty($this->event['account'])) {
                    Yii::$app->response->content = "Do not handle this event";
                    return true;
                } else {
                    return $this->processBalanceAvailable();
                }
                break;
            case self::EVENT_CHARGE_SUCCEEDED:
                return $this->processChargeSucceeded();
                break;
            case self::EVENT_CHARGE_FAILED:
                return $this->processChargeFailed();
                break;
            case self::EVENT_CHARGE_REFUNDED:
                return $this->processRefundSuccess();
                break;
            case self::EVENT_PAYOUT_SUCCEEDED:
            case self::EVENT_PAYOUT_FAILED:
                return $this->processPayout();
            case self::EVENT_ACCOUNT_UPDATED:
                return $this->accountUpdated();
                break;
        }
        // Return true to prevent Stripe from sending this request again
        return true;
    }


    public function accountUpdated()
    {
        $event = $this->event;

        /**
         * @var $paymentAccount PaymentAccount
         */
        $paymentAccount = PaymentAccount::find()->andWhere(['paymentAccountId' => $event['account']])->one();

        if (empty($paymentAccount)) {
            \Yii::error('Account updated webhook: payment account ' . $event['account'] . ' not found.', 'payment');
            return true;
        }

        if (empty($event['data']['object']['capabilities']) || !is_array($event['data']['object']['capabilities'])) {
            \Yii::error('Invalid capability format', 'payment');
            // Return true to prevent Stripe from sending this request again
            return true;
        }
        $paymentAccount->capabilities = $this->event['data']['object']['capabilities'];

        $generalRequirements = $this->event['data']['object']['individual']['requirements']['currently_due'] ?? null;

        if (!empty($generalRequirements) || !$paymentAccount->checkCapabilities()) {
            $paymentAccount->updatesRequired = true;
        } else {
            $paymentAccount->updatesRequired = false;
        }

        if (!$paymentAccount->save(false)) {
            \Yii::error('Error during save payment account.' . json_encode($paymentAccount), 'payment');
        }

        return true;
    }

    protected function mainPlatformPayout(): void
    {
        Yii::info('Process main platform payout.', 'payment');
        $id = $this->event['data']['object']['id'];
        /**
         * @var PlatformPayout $payout
         */
        $payout = PlatformPayout::find()->byStripeId($id)->limit(1)->one();

        if (empty($payout)) {
            throw new \yii\base\Exception("Payout with id = {$id} was not found in database.");
        }

        Yii::info('Main platform payout id = ' . $payout->id, 'payment');

        switch ($this->event['type']) {
            case self::EVENT_PAYOUT_SUCCEEDED:
                $newStatus = PlatformPayout::STATUS_SUCCESS;
                break;
            case self::EVENT_PAYOUT_FAILED:
                $newStatus = Transaction::STATUS_ERROR;
                break;
            default:
                throw new \yii\base\Exception('Invalid type of payout event.');
                break;
        }
        $payout->status = $newStatus;
        Yii::info('New status: ' . $newStatus, 'payment');
        if ($payout->save(false)) {
            Yii::info('Updated.', 'payment');
        } else {
            Yii::error('failed to update main platform payout.', 'payment');
        }
    }

    protected function connectedAccountPayout(): void
    {
        Yii::info('Process connected account payout.', 'payment');
        $id = $this->event['data']['object']['id'];
        /**
         * payout.paid can be sent even in case of error so payout.failed webhook has greater priority
         * That means that status can be changed from SUCCESS to ERROR but NOT rom ERROR to SUCCESS - info from stripe support
         * See comments here https://heytutor.atlassian.net/browse/HT-704
         **/

        /**
         * @var Transaction $transaction
         */
        $transaction = Transaction::find()->whereExternalTransactionId($id)->statusNotError()->limit(1)->one();

        if (!empty($transaction)) {
            Yii::info('Payout transaction id = ' . $transaction->id, 'payment');
            switch ($this->event['type']) {
                case self::EVENT_PAYOUT_SUCCEEDED:
                    $newStatus = Transaction::STATUS_SUCCESS;
                    break;
                case self::EVENT_PAYOUT_FAILED:
                    $newStatus = Transaction::STATUS_ERROR;
                    $startProcessDate = Yii::$app->settings->get('payout', 'startProcessErrorsFrom');

                    if ($startProcessDate) {
                        /**
                         * @var Formatter $formatter
                         */
                        $formatter = Yii::$app->formatter;
                        $transactionDate = \DateTime::createFromFormat(
                            $formatter->MYSQL_DATETIME,
                            $transaction->createdAt
                        );
                        $compareDate = \DateTime::createFromFormat($formatter->MYSQL_DATETIME, $startProcessDate);


                        if ($transactionDate > $compareDate) {
                            $errorObject = new \stdClass();
                            $errorObject->error = new \stdClass();
                            $errorObject->error->code = $this->event['data']['object']['failure_code'] ?? 'empty code';
                            $errorObject->error->message = $this->event['data']['object']['failure_message'] ?? '';
                            $payoutError = new PayoutError($errorObject, $transaction);
                            $payoutError->sendEmail();
                        }
                    }
                    break;
                default:
                    throw new \yii\base\Exception('Invalid type of payout event.');
                    break;
            }
            Yii::info('New transaction status = ' . $newStatus, 'payment');
            $transaction->status = $newStatus;
            $this->processSecondResponse($transaction);
            $transaction->save(false);
        } else {
            Yii::info('Payout with external transaction id = ' . $id .  " not found in db", 'payment');
        }
    }

    public function processPayout(): bool
    {
        if (empty($this->event['data']['object']['id'])) {
            throw new \yii\base\Exception('Empty Id property.');
        }

        if ($this->event['account'] ?? false) {
            $this->connectedAccountPayout();
        } else {
            $this->mainPlatformPayout();
        }
        // Return true to prevent Stripe from sending this request again
        return true;
    }


    /**
     * @return bool
     */
    protected function processRefundSuccess()
    {
        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            if (empty($this->event['data']['object']['refunds']['data'])) {
                throw new \yii\base\Exception('Empty refunds property.');
            }
            $refunds = $this->event['data']['object']['refunds']['data'];

            //getting external ids of refunds
            $refundIds = array_column($refunds, 'id');

            $refundStatuses = [];
            foreach ($refunds as $refund) {
                $refundStatuses[$refund['id']] = $refund['status'];
            }

            //getting transaction models for refunds
            $transactions = Transaction::find()->whereExternalTransactionId($refundIds)->all();

            foreach ($transactions as $transaction) {
                //skip success refunds
                if ($transaction->status === Transaction::STATUS_SUCCESS) {
                    continue;
                }

                $this->processSecondResponse($transaction);

                /**
                 * process Pending status as Success stripe explanation below:
                 * If you're still only receiving the pending status rather than success, you can treat pending the same
                 * as success. Sometimes refunds only return that status though I can understand it's not exactly intuitive.
                 */
                if (in_array($refundStatuses[$transaction->transactionExternalId], [Module::REFUND_STATUS_SUCCEED, Module::REFUND_STATUS_PENDING])) {
                    $transaction->status = Transaction::STATUS_SUCCESS;

                    //specific actions for different types of refunds, add more conditions using "else if" if it needs
                    if ($transaction->isPartialRefundOfGroupTransaction()) {
                        //change lesson status
                        $transaction->lesson->saveRefundedStatus();
                        //back funds to client balance
                        Module::createRefundClientBalance($transaction);
                    }
                } else {
                    $transaction->status = Transaction::STATUS_ERROR;
                }

                if (!$transaction->save(false)) {
                    throw new \Exception('Failed to save transaction id = ' . $transaction->id);
                }
            }
        } catch (\Throwable $exception) {
            $dbTransaction->rollBack();
            Yii::error($exception->getMessage() . Json::encode($this->event), 'payment');
            return false;
        }
        $dbTransaction->commit();
        return true;
    }

    protected function processBalanceAvailable()
    {
        $event = $this->event;

        /**
         * @var $paymentAccount PaymentAccount
         */
        $paymentAccount = PaymentAccount::find()->andWhere(['paymentAccountId' => $event['account']])->one();

        if (!$paymentAccount) {
            Yii::error('No such payment account found. Event: ' . Json::encode($event), 'payment');
            // Return true to prevent Stripe from sending this request again
            return true;
        }

        $tutor = $paymentAccount->tutor;

        Yii::info('Process balance available webhook for account with id ' . $tutor->id, 'payment');
        $balance = $paymentAccount->paymentAccountBalance;

        if (!$balance) {
            $balance = new PaymentAccountBalance([
                'paymentAccountId' => $paymentAccount->id,
            ]);
        }

        $amount = $event['data']['object']['available'][0]['amount'] ?? 0;
        $amount = Payment::fromStripeAmount($amount);
        Yii::info('Current balance amount: ' . $amount, 'payment');

        $balance->balance = $amount;
        if (!$balance->save()) {
            Yii::error('Failed to save Payment Account Balance. Errors: : ' . Json::encode($balance->getErrors()), 'payment');
        }

        if ($amount > 0) {
            /**
             * @var Formatter $formatter
             */
            $formatter = Yii::$app->formatter;
            $condition = "-1 day";
            $forLast24Hours = (new \DateTime('now'))
                ->modify($condition)
                ->format($formatter->MYSQL_DATETIME);

            $isHasPayoutWithErrors = Transaction::find()
                ->byLastTutorTransfer($tutor)
                ->whereStatusError()
                ->orderByLast()
                ->byCreatedAt('>=', $forLast24Hours)
                ->exists();

            //if wasn't failed payout for last 24 hours
            if (!$isHasPayoutWithErrors) {
                $task = new Job([
                    'route' => 'notification/balance-transfer',
                    'data' => ['tutorId' => $tutor->id]
                ]);
                Yii::$app->queue->post($task);
            } else {
                Yii::info('Tutor has failed payouts today. Do not create new payout transaction.', 'payment');
            }
        }

        if ($amount < 0) {
            $task = new Job([
                'route' => 'notification/negative-balance-appeared',
                'data' => [
                    'tutorId' => $tutor->id,
                    'paymentAccountBalanceId' => $balance->id,
                ]
            ]);
            Yii::$app->queue->post($task);
        }

        $task = new Job([
            'route' => 'payment/check-lessons-transfers-status',
            'data' => [
                'tutorId' => $tutor->id,
            ]
        ]);
        Yii::$app->queue->post($task);
        Yii::info('Lesson status check has been added to queue.', 'payment');


        return true;
    }

    protected function processChargeFailed()
    {
        $transaction = $this->findTransactionForChargeHandler();
        if (!$transaction) {
            return false;
        }
        if ($transaction->status !== Transaction::STATUS_PENDING) {
            // No need to process non-pending transactions. Saving logs just in case
            Yii::info('Prevent further processing of the event since transaction is not a pending one. Transaction #' . $transaction->id . '. Status: ' . $transaction->status, 'payment');
            return true;
        }
        $this->processSecondResponse($transaction);
        $transaction->status = Transaction::STATUS_ERROR;
        if (!$transaction->save()) {
            Yii::error('Failed to updated transaction in ChargeFailed webhook, transactionId = ' . $transaction->id);
            return false;
        }

        //do not process decline charge for group transaction: group transaction doesn't have related student to notify
        //do not create new transaction when group transaction failed
        //set related transfers statuses to NEW in case of error (they should be processed in the next group payment)
        if ($transaction->isGroupChargeTransaction()) {
            foreach ($transaction->transfers as $transfer) {
                Transaction::updateAll(['status' => Transaction::STATUS_NEW], ['id' => $transfer->id]);
            }
            return true;
        }

        ChargeHandlerService::processDeclineCharges($transaction);

        $newTransaction = new Transaction();
        $newTransaction->setAttributes(
            $transaction->getAttributes([
                'studentId',
                'tutorId',
                'parentId',
                'processDate',
                'fee',
                'amount',
                'type',
                'objectId',
                'objectType',
                'billingCycleStatus',
            ]),
            false
        );
        $newTransaction->status = Transaction::STATUS_NEW;
        $newTransaction->parentId = $transaction->id;
        $result = $newTransaction->save();
        if (!$result) {
            Yii::error('processChargeFailed():Error creating new transaction (validation error). ' . json_encode($newTransaction->getErrors()), 'payment');
        }
        return $result;
    }

    //save both response data
    protected function processSecondResponse($transaction)
    {
        $newResponse = [
            'previousResponse' => $transaction->response,
            'newResponse' => $this->event['data']['object'],
        ];

        $transaction->response = $newResponse;
    }


    protected function processChargeSucceeded()
    {
        $transaction = $this->findTransactionForChargeHandler();
        if ($transaction) {
            if ($transaction->status !== Transaction::STATUS_PENDING) {
                // No need to process non-pending transactions. Saving logs just in case
                Yii::info('Prevent further processing of the event since transaction is not a pending one. Transaction #' . $transaction->id . '. Status: ' . $transaction->status, 'payment');
                return true;
            }

            $this->processSecondResponse($transaction);
            $transaction->status = Transaction::STATUS_SUCCESS;


            if ($transaction->save()) {
                //prevent run code for group transactions
                if ($transaction->isGroupChargeTransaction()) {
                    $relatedTransfers = $transaction->getTransfers()->all();
                    //for each transaction which has been participated in calculating totals
                    foreach ($relatedTransfers as $transfer) {
                        if (empty($transfer)) {
                            throw new Exception('Lesson transfer with id ' . $transfer->id . ' hasn\'t been found.');
                        }
                        Yii::info('Process transfer with id = ' . $transfer->id);
                        $transferHandler = new TransferHandlerService();
                        $transferHandler->processLessonTransfer($transfer, $transaction->transactionExternalId);
                    }
                    return true;
                }
                $lesson = $transaction->lesson;
                $lesson->transactionComplete();
                if (!$lesson->save(false)) {
                    return false;
                }

                $moduleTransaction = Yii::$app->getModule('payment');
                $moduleTransaction->eventChargeSuccess($transaction);
                return true;
            }
            return false;
        }
        return false;
    }

    protected function findTransactionForChargeHandler()
    {
        $event = $this->event;

        if (empty($event['data']['object']['object']) || $event['data']['object']['object'] != 'charge') {
            Yii::error('No Charge object provided in webhook', 'payment');
            // No Charge object provided in webhook
            return false;
        }

        /**
         * @var $transaction Transaction
         */
        $transaction = Transaction::find()
            ->andWhere(['transactionExternalId' => $event['data']['object']['id']])
            ->one();
        if (!$transaction) {
            Yii::error('No transaction found. id from webhook = ' . $event['data']['object']['id'], 'payment');
            return false;
        }
        return $transaction;
    }

    protected function checkStripeSignature($payload)
    {
        if (!Yii::$app->payment->webhookSecret) {
            // No need to check secret in case none provided
            return true;
        }

        $signatureHeader = Yii::$app->request->getHeaders()->get('stripe-signature');
        $signatureParts = explode(',', $signatureHeader);
        foreach ($signatureParts as $signaturePart) {
            list($key, $value) = explode('=', $signaturePart);
            if ($key == 't') {
                $timestamp = $value;
            }
            if ($key == 'v1') {
                $signature = $value;
            }
        }

        if (empty($timestamp) || empty($signature)) {
            return false;
        }

        $signedPayload = $timestamp . '.' . $payload;

        $expectedSignature = hash_hmac("sha256", $payload, Yii::$app->payment->webhookSecret);

        return $this->secureCompare($signedPayload, $expectedSignature);
    }

    /**
     * Copied from newer Stripe package.
     *
     * Compares two strings for equality. The time taken is independent of the
     * number of characters that match.
     *
     * @param string $a one of the strings to compare.
     * @param string $b the other string to compare.
     * @return bool true if the strings are equal, false otherwise.
     */
    protected function secureCompare($a, $b)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($a, $b);
        } else {
            if (strlen($a) != strlen($b)) {
                return false;
            }

            $result = 0;
            for ($i = 0; $i < strlen($a); $i++) {
                $result |= ord($a[$i]) ^ ord($b[$i]);
            }
            return ($result == 0);
        }
    }
}

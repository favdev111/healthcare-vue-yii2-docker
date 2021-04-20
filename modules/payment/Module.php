<?php

namespace modules\payment;

use common\events\NotificationEvent;
use common\helpers\QueueHelper;
use console\components\Queue;
use modules\account\models\Account;
use modules\account\models\ClientBalanceTransaction;
use modules\account\models\Lesson;
use modules\payment\models\CardInfo;
use modules\payment\models\Transaction;
use Yii;
use yii\base\Event;

class Module extends \common\components\Module
{
    const EVENT_CHARGE_SUCCESS = 'chargeSuccess';
    const EVENT_CARD_DECLINE = 'cardDecline';
    const EVENT_LEAVE_REVIEW = 'leaveReview';
    const EVENT_TRANSFER_SUCCESS = 'transferSuccess';
    const EVENT_NEGATIVE_BALANCE_APPEARED = 'negativeBalanceAppeared';
    const EVENT_BALANCE_RESTORED = 'balanceRestored';
    const EVENT_REFUND_PROCESSED = 'refundProcessed';
    const EVENT_UNAPPROVED_TRANSACTION_APPEND = 'unapprovedTransactionAppend';
    const EVENT_NEW_ACTIVE_CARD = 'newActiveCard';
    const EVENT_PROCESS_PENDING_TRANSACTIONS = 'processPendingTransactions';
    const EVENT_NEW_CARD = 'newCard';
    const EVENT_CARD_DELETED = 'cardDeleted';
    const EVENT_NEW_TRANSACTION = 'newTransaction';

    const DEFAULT_COMMISSION = 20;

    const REFUND_STATUS_SUCCEED = 'succeeded';
    const REFUND_STATUS_PENDING = 'pending';


    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultModelClasses()
    {
        return [
            'Account' => 'modules\payment\models\Account',
            'CardInfo' => 'modules\payment\models\CardInfo',
            'BankAccount' => 'modules\payment\models\BankAccount',
            'PaymentCustomer' => 'modules\payment\models\PaymentCustomer',
            'Transaction' => models\Transaction::class,
        ];
    }

    public function eventChargeSuccess($transaction)
    {
        $event = new NotificationEvent();
        $event->transaction = $transaction;
        Event::trigger(self::className(), self::EVENT_CHARGE_SUCCESS, $event);
    }

    public function eventUnapprovedTransactionAppend(Transaction $transaction)
    {
        $event = new NotificationEvent();
        $event->transaction = $transaction;
        Event::trigger(self::className(), self::EVENT_UNAPPROVED_TRANSACTION_APPEND, $event);
    }

    public function eventTransferSuccess(Transaction $transaction)
    {
        $event = new NotificationEvent();
        $event->transaction = $transaction;
        Event::trigger(self::className(), self::EVENT_TRANSFER_SUCCESS, $event);
    }

    public function eventCardDecline(Transaction $charge)
    {
        $event = new NotificationEvent();
        $event->lesson = $charge->isLesson() ? $charge->lesson : null;
        $event->tutor = $charge->tutor;
        $event->student = $charge->student;
        Event::trigger(self::className(), self::EVENT_CARD_DECLINE, $event);
    }

    public function eventLeaveReview(Lesson $lesson)
    {
        $event = new NotificationEvent();
        $event->lesson = $lesson;
        Event::trigger(self::className(), self::EVENT_LEAVE_REVIEW, $event);
    }

    /**
     * @param Transaction $transaction
     */
    public function eventRefundProcessed(Transaction $transaction)
    {
        $event = new NotificationEvent();
        $event->transaction = $transaction;
        Event::trigger(self::className(), self::EVENT_REFUND_PROCESSED, $event);
    }

    /**
     * @param Account $tutor
     */
    public function eventNegativeBalanceAppeared(Account $tutor)
    {
        $event = new NotificationEvent();
        $event->tutor = $tutor;
        Event::trigger(self::className(), self::EVENT_NEGATIVE_BALANCE_APPEARED, $event);
    }

    /**
     * @param Account $account
     */
    public function eventNewActiveCard(Account $account)
    {
        $event = new NotificationEvent();
        $event->student = $account;
        Event::trigger(self::className(), self::EVENT_NEW_ACTIVE_CARD, $event);
        Event::trigger(self::className(), self::EVENT_PROCESS_PENDING_TRANSACTIONS, $event);
    }

    /**
     * @param Account $account
     */
    public function eventNewActivePaymentBankAccountOrCard(Account $account)
    {
        foreach ($account->clients as $client) {
            $event = new NotificationEvent();
            $event->student = $client;
            Event::trigger(self::className(), self::EVENT_PROCESS_PENDING_TRANSACTIONS, $event);
        }
    }

    /**
     * @param CardInfo $card
     */
    public function eventNewCard(CardInfo $card)
    {
        $event = new NotificationEvent();
        $event->student = $card->paymentCustomer->account;
        $event->card = $card;
        Event::trigger(self::className(), self::EVENT_NEW_CARD, $event);
    }

    /**
     * @param Account $account
     */
    public function eventCardDeleted($account)
    {
        $event = new NotificationEvent();
        $event->account = $account;
        Event::trigger(self::className(), self::EVENT_CARD_DELETED, $event);
    }

    /**
     * @param Transaction $transaction
     */
    public function eventNewTransaction(Transaction $transaction)
    {
        $event = new NotificationEvent();
        $event->transaction = $transaction;
        Event::trigger(self::className(), self::EVENT_NEW_TRANSACTION, $event);
    }

    /**
     * @param Account $tutor
     */
    public function eventBalanceRestored(Account $tutor)
    {
        $event = new NotificationEvent();
        $event->tutor = $tutor;
        Event::trigger(self::className(), self::EVENT_BALANCE_RESTORED, $event);
    }

    public function hasVerifiedPaymentMethod($userId = null)
    {
        if (is_null($userId) && Yii::$app->user->isGuest) {
            return false;
        }

        /**
         * @var $account Account
         */
        if (is_null($userId)) {
            $account = Yii::$app->user->identity;
        } else {
            $accountModel = Yii::$app->account->getModel('account');
            $account = $accountModel::findOne($userId);
            if (!$account) {
                return false;
            }
        }
        if ($account->isPatient()) {
            return $account->isVerified();
        }
        if ($account->isTutor()) {
            return $account->paymentAccount && $account->paymentAccount->verified;
        }

        return false;
    }

    /**
     * @param $client
     * @param int $chargeAmount
     * @param bool $triggerPackageCharge
     */
    public function processClientTransaction($client, $chargeAmount = 0, $triggerPackageCharge = false)
    {
        if (!$client->paymentCustomer || (!$client->paymentCustomer->autorenew && !$triggerPackageCharge) || !$client->paymentCustomer->activeCard) {
            // No need to proceed for account without autorenew
            return;
        }

        $fillBalanceAmount = $client->getFillBalanceAmount();
        if ($chargeAmount > $fillBalanceAmount || !$client->paymentCustomer->autorenew) {
            $amount = $chargeAmount;
        } else {
            $amount = $fillBalanceAmount;
        }
        if ($amount <= 0 && empty($chargeAmount)) {
            Yii::warning('Tried to fill non negative balance for client #' . $client->id . ' Balance: ' . $amount, 'b2b');
            return;
        }

        $transaction = new Transaction([
            'studentId' => $client->id,
            'tutorId' => null,
            'objectId' => $client->id,
            'objectType' => $triggerPackageCharge ? Transaction::TYPE_CLIENT_BALANCE_MANUAL_CHARGE : Transaction::TYPE_CLIENT_BALANCE_AUTO,
            'amount' => $amount,
            'type' => Transaction::STRIPE_CHARGE,
            'processDate' => date('Y-m-d'),
        ]);
        $transaction->save(false);

        if ($triggerPackageCharge) {
            //set this job with max priority
            QueueHelper::processCharge($transaction, Queue::PRIORITY_HIGHEST);
        } else {
            QueueHelper::processCharge($transaction);
        }
    }

    /*Coefficient that we use to get 100% amount*/
    private static function getFullRateCoefficient()
    {
        return (100 / (100 - static::DEFAULT_COMMISSION));
    }

    public function getCompanyCommissionCoefficientForOfferOrHire($companyCommission)
    {
        $companyCommissionCoefficient = ((100 - (static::DEFAULT_COMMISSION - $companyCommission)) / 100);
        return static::getFullRateCoefficient() * $companyCommissionCoefficient;
    }

    public function getAmountWithCompanyCommissionForOfferOrHire($tutorRate, $companyCommission)
    {
        return round($tutorRate * self::getCompanyCommissionCoefficientForOfferOrHire($companyCommission));
    }

    public function calcTutorRateFromCompanyRate($amount, $commission)
    {
        $companyPayRate = $amount;
        $companyCommissionCoefficient = ((100 - (static::DEFAULT_COMMISSION - $commission)) / 100);
        $fullRate = $companyPayRate / $companyCommissionCoefficient;
        $tutorRate = $fullRate / static::getFullRateCoefficient();

        return $tutorRate;
    }

    public static function selectTransactionStatus($refundStatus)
    {
        switch ($refundStatus) {
            case static::REFUND_STATUS_SUCCEED:
                return Transaction::STATUS_SUCCESS;
                break;
            default:
                return Transaction::STATUS_PENDING;
                break;
        }
    }

    /**
     * Part of refund process. After refund Client Balance transactions need to subtract transaction sum from
     *client balance that client get for transaction.
     * Works for general lessons refunds and partial refunds of group Transaction (because they have objectType = TYPE_LESSON too)
     * @param Transaction $transaction
     * @throws \Exception
     */
    public static function createRefundClientBalance($transaction)
    {
        if (!$transaction->student->isPatient() || !$transaction->isLesson()) {
            return;
        }
        //refund for sum that paid by client
        $clientBalanceAmount = abs($transaction->lesson->calculateClientPrice());

        /*Create Client-balance transaction with type Automatically */
        $clientBalanceTransaction = new ClientBalanceTransaction([
            'clientId' => $transaction->studentId,
            'amount' => $clientBalanceAmount,
            'type' => ClientBalanceTransaction::TYPE_TRANSACTION_AUTO,
            'transactionId' => $transaction->id,
            'hide' => 1,
        ]);
        if (!$clientBalanceTransaction->save()) {
            Yii::error('Failed to save client balance transaction (REFUND). Errors: ' . json_encode($clientBalanceTransaction->getErrors()), 'payment');
            throw new \Exception('Failed to save client balance transaction (REFUND).');
        }
    }
}

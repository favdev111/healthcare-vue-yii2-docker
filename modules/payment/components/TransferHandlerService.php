<?php

namespace modules\payment\components;

use common\helpers\EmailHelper;
use modules\payment\components\interfaces\TransferPaymentInterface;
use modules\payment\models\PayoutError;
use modules\payment\Module;
use Yii;
use modules\account\models\Account;
use modules\payment\models\Transaction;
use yii\base\Exception;

/**
 * Use only on console context
 * Class TransferHandlerService
 * @package modules\payment\components
 * @todo: need refactoring. run() Method is a copypast from payment/payment/tutor-transfer
 */
class TransferHandlerService extends AbstractHandlerService
{
    const AMOUNT_ZERO_VALUE = 0;
    const FEE_ZERO_VALUE = 0;

    /**
     * TransferHandlerService constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->setTransferPayment(Yii::$app->payment);
    }

    /**
     * @param TransferPaymentInterface $payment
     * @return $this
     */
    public function setTransferPayment(TransferPaymentInterface $payment)
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * @inheritdoc
     * @todo: refactor this copypaste. 1) Move logging logic to separate class. 2) Move ActiveQuery logic to AQ classes 3) Move general logic to AbstractHandlerService
     */
    public function run()
    {
        /**
         * @var $accounts Account[]
         */
        $accounts = Account::find()
            ->joinWith('paymentAccount')
            ->andWhere(['not', ['paymentAccountId' => null]])
            ->all();

        foreach ($accounts as $account) {
            $this->processAccountTransfers($account);
        }
    }

    /**
     * @param Account $account
     */
    public function processAccountTransfers(Account $account)
    {
        Yii::info('Transfer for tutor - ' . $account->id . ' is processing', 'payment');
        $stripeAccount = $account->paymentAccount->paymentAccountId;
        $this->sendStdout('Tutor with id: ' . $account->id);

        $balance = $this->payment->getBalance($stripeAccount);
        Yii::info("Stripe balance response for account - {$account->id} :  {$balance->toJSON()}", 'payment');

        if (empty($balance->available)) {
            $this->sendStdout("Payment error: Balance not available for tutor - {$account->id}. Stripe response: {$balance->toJSON()} \n");
            Yii::error(
                "Transfer - Balance not available for tutor - {$account->id}. Stripe response: {$balance->toJSON()} \n",
                'payment'
            );
            return;
        }

        foreach ($balance->available as $availableItem) {
            Yii::info("Processing Stripe balance item for account - {$account->id} :  " . json_encode($availableItem), 'payment');
            if ($availableItem->currency != Payment::USD_CURRENCY) {
                Yii::error('Stripe account has balance in non-USD currency. Account #' . $account->id . ' Stripe Account: ' . $stripeAccount, 'payment');
                continue;
            }
            foreach ($availableItem->source_types->keys() as $balanceSourceType) {
                $amount = $availableItem->source_types->{$balanceSourceType};
                // Process each Source balance separately
                //if balance size less that total available balance - payout only part of funds
                if (($amount > $availableItem->amount)) {
                    Yii::info("Source value  [ $balanceSourceType -> $amount] greater than total value [{$availableItem->amount}]} ", 'payment');
                    $amount = $availableItem->amount;
                }
                Yii::info("Processing Stripe balance item source for account - {$account->id} : $balanceSourceType -> $amount", 'payment');
                $this->initiateTransfer($account, $amount, $stripeAccount, $balanceSourceType);
            }
        }
        Yii::info("Processing Stripe balance finished for account - {$account->id}", 'payment');
    }

    public function initiateTransfer($account, $amount, $stripeAccount, $sourceType)
    {
        $accountId = $account->id;
        Yii::info("Processing Stripe Balance Source type $sourceType. Amount to transfer : $amount. Account - {$accountId}", 'payment');
        $curBalance = $amount;

        if ($curBalance <= self::AMOUNT_ZERO_VALUE) {
            Yii::info("Transfer - Negative balance. Account - {$accountId}. Balance is negative: $curBalance. Source Type: $sourceType\n", 'payment');
            return;
        }

        $tr = new Transaction();
        $tr->tutorId = $accountId;
        $tr->objectType = Transaction::TYPE_ACCOUNT;
        $tr->objectId = $accountId;
        $tr->processDate = date('Y-m-d');
        $tr->amount = Payment::fromStripeAmount($curBalance);
        $tr->fee = self::FEE_ZERO_VALUE;
        $tr->type = Transaction::STRIPE_TRANSFER;

        $errorMessage = null;

        try {
            $bankTransaction = $this->payment->transferToBank($curBalance, $sourceType, $stripeAccount);
            $tr->transactionExternalId = $bankTransaction->id;
            $tr->response = $bankTransaction;
            $tr->status = Transaction::STATUS_PENDING;
        } catch (\Exception $e) {
            $tr->status = Transaction::STATUS_ERROR;
            $errorMessage = $e->getMessage();
            $tr->response = json_encode($e->getMessage());

            if ($account->isTutor()) {
                $payoutError = new PayoutError(json_decode($e->getHttpBody()), $tr);
                $payoutError->sendEmail();
            }
        }
        $tr->save(false);
        if ($tr->status == Transaction::STATUS_SUCCESS) {
            /**
             * @var $moduleTransaction Module
             */
            $moduleTransaction = Yii::$app->getModule('payment');
            $moduleTransaction->eventTransferSuccess($tr);
            Yii::info("Transfer processed successfully. Account - {$accountId}. Transaction ID: $tr->id. Source Type: $sourceType\n", 'payment');
        } elseif ($tr->status == Transaction::STATUS_ERROR) {
            EmailHelper::sendMessageToAdmin('Payment error', 'Transfer ' . $tr->id . ' - ' . $errorMessage);
            Yii::error('Transfer ' . $tr->id . ' - ' . $errorMessage . ' for account : ' . $accountId, 'payment');
        }
    }

    public function processLessonTransfer(Transaction $transaction, $groupTransactionExternalId = null)
    {
        if (!$transaction->isLessonBatchPayment() && !($transaction->isLessonTransfer())) {
            throw new Exception('Method processLessonTransfer() can process only transactions with type TYPE_LESSON_BATCH_PAYMENT or transfers with type lesson (id = ' . $transaction->id . ")");
        }
        $stripeAccount = $transaction->tutor->paymentAccount->paymentAccountId;

        try {
            $this->tryProcessLessonTransfer($transaction, $stripeAccount, $groupTransactionExternalId);
        } catch (\Exception $e) {
            $transaction->status = Transaction::STATUS_ERROR;
            $transaction->response = json_encode($e->getMessage());
        }
        $transaction->save(false);
    }

    /**
     * try to process lesson transfer to tutor. Required external exception handling.
     * @param $transaction
     * @param $stripeAccount
     * @param null $groupTransactionExternalId
     */
    public function tryProcessLessonTransfer($transaction, $stripeAccount, $groupTransactionExternalId = null): void
    {
        $response = $this->payment->lessonTransferToTutor(Transaction::amountToCents($transaction->amount), $stripeAccount, $groupTransactionExternalId);
        $transaction->transactionExternalId = $response->id;
        if (empty($transaction->response)) {
            $transaction->response = $response;
        } else {
            $newResponse = [
                'previousResponse' => $transaction->response,
                'newResponse' => $response,
            ];
            $transaction->response = $newResponse;
        }
        $transaction->status = Transaction::STATUS_PENDING;
    }
}

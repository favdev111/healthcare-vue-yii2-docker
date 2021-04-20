<?php

namespace common\components;

use Codeception\Util\HttpCode;
use common\helpers\EmailHelper;
use common\helpers\Html;
use common\helpers\Url;
use modules\payment\components\Payment;
use modules\payment\models\PaymentProcess;
use modules\payment\models\PlatformPayout;
use modules\payment\models\ProcessedLessonTransfer;
use modules\payment\models\Transaction;
use Stripe\Account;
use Stripe\Balance;
use Stripe\Stripe;
use Stripe\Transfer;
use yii\base\Component;
use yii\base\Exception;
use yii\web\Response;

/**
 * Class StripePlatformAccount
 * @property-read integer $platformAvailableBalance - executes request to stripe balance API each time
 * @package common\components
 */
class StripePlatformAccount extends Component
{
    const TRANSFER_ERROR_REASON_FUNDS = 'balance_insufficient';
    protected function isErrorReasonFunds($exception): bool
    {
        if (!empty($exception->httpBody)) {
            $body = json_decode($exception->httpBody);
            if (!empty($body->error->code) && $body->error->code == static::TRANSFER_ERROR_REASON_FUNDS) {
                return true;
            }
        }
        return false;
    }

    protected function logInfo(string $message): void
    {
        \Yii::info($message, 'platform-payments');
    }

    protected function logError(string $message): void
    {
        \Yii::error($message, 'platform-payments');
    }

    /**
     * Save current available balance and change status to Complete
     * @param PaymentProcess $paymentProcess
     * @throws Exception
     */
    protected function completeProcess(PaymentProcess $paymentProcess): void
    {
        $paymentProcess->availableBalanceAfterPaymentProcess = $this->platformAvailableBalance;
        $paymentProcess->status = PaymentProcess::STATUS_COMPLETE;
        if (!$paymentProcess->save()) {
            throw new Exception(
                'Failed to complete payment process.'
                . ($paymentProcess->hasErrors) ? json_encode($paymentProcess->getErrors()) : ''
            );
        }
        $this->logInfo("Process completed. Earned today  {$paymentProcess->earnedToday}$. Current balance {$paymentProcess->availableBalanceAfterPaymentProcess}$");
    }

    protected function rechargeFailedLessonTransfers(array $failedTransfers)
    {
        foreach ($failedTransfers as $transfer) {
            $newTransfer = Transaction::reCharge($transfer);
            if ($newTransfer) {
                $this->logInfo('Created new transaction for transfer with id = ' . $transfer->id . ". New transfer id is {$newTransfer->id}");
            } else {
                $this->logError('Failed to re-charge lesson transfer with id = ' . $transfer->id);
            }
        }
    }

    protected function sendPaymentIssueEmails(PaymentProcess $paymentProcess)
    {
        $text = "There are some issues during payment process with id {$paymentProcess->id}.
         Please take a look for payment details.";
        EmailHelper::sendMessageToDevTeam('Manual actions required: payment issues.(' . YII_ENV . ')', $text);
    }


    /**
     * Send email to admin in case there is not enough funds
     * @param  array $failedTransfers
     * @return bool
     */
    protected function sendNotEnoughFoundsEmail(array $failedTransfers): bool
    {
        $sum = 0;
        $links = [];
        foreach ($failedTransfers as $transfer) {
            /**
             * @var Transaction $transfer
             */
            $sum += $transfer->amount;
            $links[] = Url::toRoute(['/backend/payment/transaction-enterprise/view', 'id' => $transfer->id]);
        }

        $text = "Ryan, Skyler!<br><br>You have insufficient funds in your Stripe account. Payments to tutors are paused. Please refill your balance for {$sum}.<br>";
        $text .= 'Failed transactions list:<br>';
        foreach ($links as $link) {
            $text .= Html::a($link, $link) . "<br>";
        }
        return EmailHelper::sendMessageToAdmin('Negative payments balance ASAP!', $text);
    }

    /**
     * Process lesson transfers and updates paymentProcessModel
     * @param PaymentProcess $paymentProcess
     * @return float - sum of success processed lesson transfers
     * @throws Exception
     */
    protected function processLessonsTransfers(PaymentProcess $paymentProcess): float
    {
        $this->logInfo('Start process lessons transfers');
        $hasErrors = false;
        $notEnoughFunds = false;
        $totalPaid = 0;

        $failedTransfers = [];

        $lessonsTransfers = Transaction::find()->lessonTransfer()->byStatus(Transaction::STATUS_NEW);
        foreach ($lessonsTransfers->each() as $lessonTransfer) {
            $logMessage = 'Lesson transfer with id ' . $lessonTransfer->id . '. ';
            /**
             * @var Transaction $lessonTransfer
             */
            try {
                $stripeAccount = $lessonTransfer->tutor->paymentAccount->paymentAccountId;
                \Yii::$app->transferHandlerService->tryProcessLessonTransfer($lessonTransfer, $stripeAccount);
                if ($lessonTransfer->save(false)) {
                    $relation = new ProcessedLessonTransfer([
                        'paymentProcessId' => $paymentProcess->id,
                        'lessonTransferId' => $lessonTransfer->id,
                    ]);
                    $totalPaid += $lessonTransfer->amount;

                    if (!$relation->save()) {
                        throw new Exception('Can not create relation between payment process and lesson transfer.');
                    }
                    $logMessage .= "Success. Amount = {$lessonTransfer->amount}, relation id = {$relation->id}";
                } else {
                    throw new Exception("Failed to update data for transfer with id =  {$lessonTransfer->id}. Model data: " . json_encode($lessonTransfer));
                }
                $this->logInfo($logMessage);
            } catch (\Throwable $e) {
                $lessonTransfer->status = Transaction::STATUS_ERROR;
                //write error to response
                if (empty($lessonTransfer->response)) {
                    $lessonTransfer->response = $e->getMessage();
                } else {
                    $newResponse = [
                        'previousResponse' => $lessonTransfer->response,
                        'newResponse' => $e->getMessage(),
                    ];
                    $lessonTransfer->response = $newResponse;
                }
                $lessonTransfer->save(false);
                $logMessage .= "Failed. {$e->getMessage()}";

                $hasErrors = true;
                if ($this->isErrorReasonFunds($e)) {
                    $notEnoughFunds = true;
                }
                $this->logError($logMessage);

                $failedTransfers[] = clone $lessonTransfer;
            }
        }

        //update payment process data
        $paymentProcess->hasErrors = $hasErrors;
        $paymentProcess->isNotEnoughFunds = $notEnoughFunds;
        $paymentProcess->paidToday = $totalPaid;
        if (!$paymentProcess->save()) {
            //write whole process object to logs to prevent missing information
            $message = "Error during saving payment process after process transfers: Process details:" . json_encode($paymentProcess);
            throw new Exception($message);
        }
        $this->logInfo('End process lessons transfers');

        $this->rechargeFailedLessonTransfers($failedTransfers);

        if ($notEnoughFunds) {
            $this->sendNotEnoughFoundsEmail($failedTransfers);
        }
        return (float)$totalPaid;
    }

     /**
     * Retrieve available platform balance in USD
     * @return float
     * @throws Exception
     */
    public function getPlatformAvailableBalance(): float
    {
        $balanceObject = \Yii::$app->payment->retrievePlatformBalanceObject();
        /**
         * @var Formatter $formatter
         */
        $formatter = \Yii::$app->formatter;
        foreach ($balanceObject->available as $balance) {
            if ($balance->currency == strtolower($formatter->currencyCode)) {
                $stripeBalance = $balance->amount;
            }
        }
        if (!isset($stripeBalance)) {
            throw new Exception('Can not retrieve platform balance. Available.amount property hasn\'t been set.');
        }
        return (float)Payment::fromStripeAmount($stripeBalance);
    }

    /**
     * Create first row in payment_process in case main platform payment process
     * fill earnedToday with current platform available balance
     * returns true in case of success
     * @return bool
     */
    public function createFirstPaymentProcess(): bool
    {
        $currentBalance = $this->platformAvailableBalance;
        $paymentProcess = new PaymentProcess();
        $paymentProcess->earnedToday = $currentBalance;
        $paymentProcess->availableBalanceAfterPaymentProcess = $currentBalance;
        $paymentProcess->status = PaymentProcess::STATUS_COMPLETE;
        return $paymentProcess->save(false);
    }

    /**
     * Create payout from main platform stripe account to related BA
     * @param float $amount - in USD
     * @param int $paymentProcessId
     * @param string $sourceType
     * @throws Exception
     */
    public function payout(float $amount, $sourceType, $paymentProcessId = null): void
    {
        \Stripe\Stripe::setApiKey(\Yii::$app->payment->privateKey);

        $result = \Yii::$app->payment->transferToBank((int)Payment::toStripeAmount($amount), $sourceType);

        $responseCode = $result->getLastResponse()->code ?? null;
        if (empty($responseCode)) {
            throw new Exception('Can\'t find response code.');
        }
        if ($responseCode == HttpCode::OK) {
            $payout = new PlatformPayout([
                'paymentProcessId' => $paymentProcessId,
                'status' => PlatformPayout::STATUS_PENDING,
                'stripeId' => $result->id,
                'response' => json_encode($result->getLastResponse()),
                'amount' => $amount,
                'source' => PlatformPayout::$sourceCodes[$sourceType]
            ]);

            if (!$payout->save()) {
                $errors = $payout->hasErrors() ? json_encode($payout->getErrors()) : null;
                throw new Exception("Failed to save payout. Validation errors: {$errors} \nData:" . json_encode($payout));
            }
            $this->logInfo("Payout created with id = {$payout->id}. stripeId = {$result->id} amount = $amount");
        } else {
            throw new Exception('Response code is not 200.');
        }
    }

    /**
     * Create payouts and store it in DB, calculate payout amount for today
     * @param float $payoutAmount
     * @param int $paymentProcessId
     */
    public function processPayout(float $payoutAmount, int $paymentProcessId): void
    {
        $this->logInfo("Start process payout.");

        //do not stop payment process in case payout error
        try {
            $balanceObject = \Yii::$app->payment->retrievePlatformBalanceObject();
            /**
             * @var Formatter $formatter
             */
            $formatter = \Yii::$app->formatter;

            $this->logInfo('Balances info:' . json_encode($balanceObject->available));
            //getting available amount in each source type
            foreach ($balanceObject->available as $balance) {
                if ($balance->currency == strtolower($formatter->currencyCode)) {
                    $sourceTypes = $balance->source_types;
                }
            }

            if (!isset($sourceTypes)) {
                throw new Exception('Source types has not been defined from balance response');
            }

            $cardSourceFunds = Payment::fromStripeAmount($sourceTypes->{PlatformPayout::SOURCE_CARD_LABEL});
            $this->logInfo('Payout sum is ' . $payoutAmount);

            //if we can payout whole sum form card source and in card source balance isn't 0
            if ($cardSourceFunds >= $payoutAmount && ($cardSourceFunds != 0)) {
                $this->logInfo('Using only card type payout. One payout should be created with source_type card.');
                $this->payout($payoutAmount, PlatformPayout::SOURCE_CARD_LABEL, $paymentProcessId);
            } else {
                $this->logInfo('Try to payout both source types.');
                //payout all funds from card source
                if ($cardSourceFunds) {
                    $this->logInfo('Creating payout with source type Card.' . " Truing to payout $cardSourceFunds");
                    $this->payout($cardSourceFunds, PlatformPayout::SOURCE_CARD_LABEL, $paymentProcessId);
                } else {
                    $this->logInfo('There is no funds on card source type.');
                }
                $this->logInfo('Creating payout with source type BA.');
                //and all funds we need from BA
                $this->payout($payoutAmount - $cardSourceFunds, PlatformPayout::SOURCE_BA_LABEL, $paymentProcessId);
            }
        } catch (\Throwable $ex) {
            $this->logError($ex->getMessage() . $ex->getTraceAsString());
            $text = "Platform payout issue during payment process with id = $paymentProcessId. Manual action required.";
            EmailHelper::sendMessageToDevTeam('Manual actions required: payment issues.(' . YII_ENV . ')', $text);
        }
    }

    /**
     * Get sum which was added to balance today
     * @param PaymentProcess $lastProcess
     * @return float
     */
    protected function calculateEarnedTodaySum(PaymentProcess $lastProcess): float
    {
        $currentBalance = $this->platformAvailableBalance;
        $result = $currentBalance - $lastProcess->availableBalanceAfterPaymentProcess;
        return ($result >= 0) ? $result : 0;
    }

    protected function sendEmailAboutSkippedPayout(string $text)
    {
        return EmailHelper::sendMessageToAdmin('Attention! Stripe Payout was canceled!', $text);
    }

    protected function isPossiblePayout(
        float $payoutAmount,
        float $totalPaid,
        float $earnedYesterday,
        PaymentProcess $paymentProcess,
        PaymentProcess $lastProcess
    ): bool {
        $totalAvailable = $this->platformAvailableBalance;

        //If yesterday payout hasn't been processed (isNotEnoughFunds = 1) OR today not enough funds do not start payout today.
        if ($paymentProcess->isNotEnoughFunds) {
            $reason = 'Payment process has transfers with "insufficient funds" error.';
        } elseif ($lastProcess->isNotEnoughFunds) {
            $reason = 'Previous payment process was with "insufficient funds" error.';
            $emailMessage = "Ryan, Skyler!<br><br> We had an issue with negative balance (insufficient funds on Stripe account). Lesson are processed, but payouts are paused. Please review your balance.";
        } elseif (!$totalAvailable) {
            $reason = 'There is no available balance for payout.';
            $emailMessage = "Ryan, Skyler!<br><br> Please review your balance on Stripe, there is no available  balance for payout";
        } elseif ($payoutAmount <= 0) {
            $reason = "Sum earned yesterday ($earnedYesterday) less than total paid sum today ($totalPaid).";
            $emailMessage = "Ryan, Skyler!<br><br> Payout skipped. Reason: total earnings for yesterday are LESS than today's payouts.";
        } elseif ($payoutAmount > $totalAvailable) {
            $reason = "There is not enough funds for payout. Calculated payout sum $payoutAmount$, available funds $totalAvailable$";
            $emailMessage = "Ryan, Skyler!<br><br> Payout was not processed since calculated payout amount ($payoutAmount$) has exceeded available balance ($totalAvailable$).";
        } else {
            return true;
        }

        $paymentProcess->cancelPayoutReason = $reason;
        $paymentProcess->save();

        $this->logInfo($reason);
        if (isset($emailMessage)) {
            $this->sendEmailAboutSkippedPayout($emailMessage);
        }

        return false;
    }

    public function runPaymentProcess()
    {
        $this->logInfo("\n\n" . 'Start new payment process.');
        //get data about last completed process
        $lastProcess = PaymentProcess::getLastCompletedProcess();
        if (empty($lastProcess)) {
            $message = 'Last payment process not found.';
            $this->logError($message);
            throw new Exception($message);
        }
        $this->logInfo("Last complete process found with id {$lastProcess->id}");
        $earnedYesterday = $lastProcess->earnedToday;

        //creating new with status "created" (0)
        $paymentProcess = new PaymentProcess();
        $paymentProcess->earnedToday = $this->calculateEarnedTodaySum($lastProcess);

        //save earned today sum
        if (!$paymentProcess->save()) {
            $message = 'Can not create new payment process.';
            $this->logError($message);
            throw new Exception($message);
        }
        $this->logInfo("New process created with id {$paymentProcess->id}");

        try {
            $totalPaid = $this->processLessonsTransfers($paymentProcess);
            $payoutAmount = $earnedYesterday - $totalPaid;
            $this->logInfo("Calculated payout amount is $payoutAmount = $earnedYesterday - $totalPaid");

            if ($this->isPossiblePayout($payoutAmount, $totalPaid, $earnedYesterday, $paymentProcess, $lastProcess)) {
                $this->processPayout($payoutAmount, $paymentProcess->id);
            }

            //save current platform balance and update status
            $this->completeProcess($paymentProcess);
        } catch (\Throwable $exception) {
            $message = $exception->getMessage() . "\n Trace:" . $exception->getTraceAsString();
            $paymentProcess->hasErrors = true;
            $paymentProcess->error = $message;
            $paymentProcess->save(false);
            $this->logError($message);
        }

        if ($paymentProcess->hasErrors) {
            $this->sendPaymentIssueEmails($paymentProcess);
        }
    }

    public static function sendUpdateDebitRequest($account, $value)
    {
        if (!empty($account->paymentAccount->paymentAccountId)) {
            \Yii::info('Update ' . $account->id, 'setDebit');
            /**
             * @var \modules\account\models\Account $account
             */
            Account::update($account->paymentAccount->paymentAccountId, [
                'debit_negative_balances' => $value,
            ]);
        }
    }
}

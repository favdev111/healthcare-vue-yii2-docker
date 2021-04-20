<?php

namespace modules\payment\controllers\console;

use modules\account\models\Account;
use modules\account\models\PaymentAccount;
use modules\payment\components\ChargeHandlerService;
use modules\payment\components\Payment;
use modules\payment\components\TransferHandlerService;
use modules\payment\models\Transaction;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;

class PaymentController extends Controller
{
    public function actionTutorTransfer()
    {
        Yii::info('start transfer action - ' . date('Y-m-d H:i:s'), 'payment');
        Yii::info('number day - ' . date('w'), 'payment');

        /**
         * @var $transferHandlerService TransferHandlerService
         */
        $transferHandlerService = Yii::$app->transferHandlerService;

        $transferHandlerService->run();

        Yii::info('end transfer action - ' . date('Y-m-d H:i:s'), 'payment');
    }

    /**
     * for cron tab, run once per day
     * process payments for companies with batchPayment flag, create and process group transaction for that companies
     * @throws Exception
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionProcessGroupTransactions()
    {
        Console::output('Looking for batch payment companies... ' . date('Y-m-d H:i:s'));
        $companies = Account::find()->isCrmAdmin()->joinWith('profile')->isBatchPayments()->all();

        if (empty($companies)) {
            Console::output('Nothing is found.');
            return;
        }

        $chargeHandlerService = new ChargeHandlerService();
        foreach ($companies as $company) {
            try {
                /**
                 * @var Account $company
                 */
                Console::output("\nProcess company with id = {$company->id} ({$company->profile->companyName})");
                //calculate total sum for company
                $totals = $company->calculateCompanyLessonSumToPay();
                if ($totals['amount'] == 0) {
                    Console::output('Total amount is 0 for company with id = ' . $company->id . ' skipping...');
                    continue;
                }
                Console::output('Totals:' . json_encode($totals));

                //create group charge
                Console::output('Creating new Group Charge');
                $groupTransaction = new Transaction();
                $groupTransaction->objectType = Transaction::TYPE_COMPANY_GROUP_PAYMENT;
                $groupTransaction->type = Transaction::STRIPE_CHARGE;
                //sum amount and fee for group transaction because there is no fee-payment in stripe for group payments
                //so it should be included in sum of charge from company to platform
                $groupTransaction->amount = $totals['amount'] + $totals['fee'];
                $groupTransaction->fee = 0;
                $groupTransaction->objectId = $company->id;
                $groupTransaction->processDate = date('Y-m-d');

                if (!$groupTransaction->save(false)) {
                    Console::output('Group Charge hasn\'t been created');
                }
                Console::output('Processing Group Charge');
                //process group charge
                if (!$chargeHandlerService->processTransaction($groupTransaction)) {
                    Console::output('Error process group transaction. id = ' . $groupTransaction->id);
                    continue;
                }

                //create relations between charge and transfers
                Yii::$app->db->createCommand()->update(
                    Transaction::tableName(),
                    [
                        'groupTransactionId' => $groupTransaction->id,
                    ],
                    [
                        'id' => $totals['transactionIds'],
                    ]
                )->execute();

                foreach ($groupTransaction->transfers as $transfer) {
                    $transfer->status = Transaction::STATUS_PENDING;
                    $transfer->save(false);
                }
            } catch (\Throwable $exception) {
                Console::output('Error company id = ' . $company->id . "\n"  . $exception->getMessage());
            }
        }
    }

    public function actionInitTransfer($accountId, $amountParam)
    {
        $account = Account::findOne($accountId);
        if (empty($account)) {
            Console::output('Can find account with id ' . $accountId);
        }
        $stripeAccount = $account->paymentAccount->paymentAccountId;

        $balance = Yii::$app->payment->getBalance($stripeAccount);

        Console::output('Balance:' . $balance->toJSON());

        if (empty($balance->available)) {
            Console::output("Empty balance");
            return;
        }

        foreach ($balance->available as $availableItem) {
            Yii::info("Processing Stripe balance item for account - {$account->id} :  " . json_encode($availableItem), 'payment');
            if ($availableItem->currency != Payment::USD_CURRENCY) {
                Console::output('Stripe account has balance in non-USD currency. Account #' . $account->id . ' Stripe Account: ' . $stripeAccount);
                continue;
            }
            foreach ($availableItem->source_types->keys() as $balanceSourceType) {
                // Process each Source balance separately
                Yii::$app->transferHandlerService->initiateTransfer($account, $amountParam, $stripeAccount, $balanceSourceType);
            }
        }
    }

    public function actionUpdateOldCapability($stripeId = null)
    {
        if ($stripeId) {
            Yii::$app->payment->addNewCapabilities($stripeId);
        } else {
            $query = PaymentAccount::find()->where("capabilities like '%legacy_payments%'");
            /**
             * @var PaymentAccount $paymentAccount
             */
            $i = 0;
            $total = (clone $query)->count();
            Console::startProgress($i, $total, 'Update capabilities for old users');
            foreach ($query->each() as $paymentAccount) {
                try {
                    Yii::$app->payment->addNewCapabilities($paymentAccount->paymentAccountId);
                } catch (\Throwable $e) {
                    Yii::error($e->getMessage() . " " . $e->getTraceAsString(), 'payment');
                }
                $i++;
                Console::updateProgress($i, $total, 'Update capabilities for old users');
            }
            Console::endProgress();
        }
    }

    public function actionDeleteAccount($stripeId = null)
    {
        Yii::$app->payment->deleteAccount($stripeId);
    }
}

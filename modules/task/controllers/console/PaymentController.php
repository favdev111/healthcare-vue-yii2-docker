<?php

namespace modules\task\controllers\console;

use common\helpers\QueueHelper;
use modules\account\models\Account;
use modules\account\models\Job;
use modules\payment\components\ChargeHandlerService;
use modules\payment\models\Transaction;
use UrbanIndo\Yii2\Queue\Worker\Controller;
use Yii;
use yii\helpers\Console;

class PaymentController extends Controller
{
    public function actionProcessCharges($studentId, $forceProcessToday)
    {
        Yii::info('start charge action for student #' . $studentId, 'payment');

        $chargeHandlerService = new ChargeHandlerService();

        $transactionsQuery = Transaction::find()->byNewTransactions()->andWhere([Transaction::tableName() . '.studentId' => $studentId])->notTransfers();

        foreach ($transactionsQuery->each(100) as $transaction) {
            QueueHelper::processCharge($transaction);
        }

        Yii::info('end charge action for student #' . $studentId, 'payment');
    }

    public function actionProcessLessonCharge($transactionId)
    {
        Yii::info('start charge action for transaction #' . $transactionId, 'payment');

        $this->processTransaction($transactionId);

        Yii::info('end charge action for transaction #' . $transactionId, 'payment');
    }

    public function actionProcessLessonTransfer(int $transactionId)
    {
        Yii::info('start transfer action for transaction #' . $transactionId, 'payment');
        $transaction = Transaction::findOne($transactionId);
        if (empty($transaction)) {
            Yii::info('start transfer action for transaction #' . $transactionId, 'payment');
                return false;
        }

        try {
            Yii::$app->transferHandlerService->processLessonTransfer($transaction);
        } catch (\Throwable $ex) {
            Yii::error("Error process transfer $transactionId :" . $ex->getMessage() . "\n" . $ex->getTraceAsString(), 'payment');
        }

        Yii::info('end transfer action for transaction #' . $transactionId, 'payment');
        return true;
    }

    public function actionFillClientBalance($transactionId)
    {
        Yii::info('start fill balance action. Transaction #' . $transactionId, 'payment');
        $this->processTransaction($transactionId, true);
        Yii::info('end fill balance action for transaction #' . $transactionId, 'payment');
    }

    protected function processTransaction($transactionId, $force = false)
    {
        $chargeHandlerService = new ChargeHandlerService();
        $transaction = Transaction::findOne($transactionId);
        $chargeHandlerService->processTransaction($transaction, $force);
    }

    public function actionCheckLessonsTransfersStatus($tutorId)
    {
        Yii::info("\nChecking transactions statuses for tutor = $tutorId", 'payment');
        $query = Transaction::find()->andWhere([
            'or',
            ['objectType' => Transaction::TYPE_LESSON_BATCH_PAYMENT],
            [
                'and',
                ['objectType' => Transaction::TYPE_LESSON],
                ['type' => Transaction::STRIPE_TRANSFER],
            ]
        ])->whereStatusPending()->andWhere(['tutorId' => $tutorId]);
        $strQuery = (clone $query)->createCommand()->getRawSql();
        Yii::info("\nLooking for transfers:$strQuery", 'payment');
        foreach ($query->each(20) as $transfer) {
            try {
                Yii::info("\nProcess check transfer with id = {$transfer->id}", 'payment');
                /**
                 * @var Transaction $transfer
                 */
                if ($transfer->isFundsAvailable()) {
                    $transfer->status = Transaction::STATUS_SUCCESS;
                    if ($transfer->save(false)) {
                        Yii::info('Funds are available, status changed to success', 'payment');
                    } else {
                        Yii::error('Failed to save transfer.', 'payment');
                    }
                } else {
                    Yii::info('Funds are still not available.', 'payment');
                }
            } catch (\Throwable $exception) {
                Yii::error($exception->getMessage() . ' ' . $exception->getTraceAsString(), 'payment');
                continue;
            }
        }
    }
}

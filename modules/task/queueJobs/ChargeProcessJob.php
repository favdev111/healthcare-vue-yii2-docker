<?php

namespace modules\task\queueJobs;

use modules\payment\components\ChargeHandlerService;
use modules\payment\models\Transaction;
use modules\task\components\RetryableJob;

/**
 * Do the same as \modules\task\controllers\console\PaymentController::actionFillClientBalance()
 * Class ChargeProcessJob
 */
class ChargeProcessJob extends RetryableJob
{
    public $transactionId;

    public function execute($queue)
    {
        $chargeHandlerService = new ChargeHandlerService();
        $transaction = Transaction::findOne($this->transactionId);
        return $chargeHandlerService->processTransaction($transaction, true);
    }
}

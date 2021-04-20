<?php

namespace common\models;

use common\components\Formatter;
use modules\payment\models\Transaction;
use modules\payment\Module;
use Yii;
use yii\base\Model;

class RefundData extends Model
{
    public $amount;
    public $transactionId;
    public $transactionModel;
    /**
     * @var bool $processGroupTransaction - allow process groupTransactions
     */
    protected $processGroupTransaction = false;

    public function beforeValidate()
    {
        $beforeValidate = parent::beforeValidate();
        if ($beforeValidate) {
            if (empty($this->transactionModel)) {
                $payment = Yii::$app->getModule('payment');
                $class = $payment->model('Transaction');
                $this->transactionModel = $class::findOne($this->transactionId);
            }
        }
        return $beforeValidate;
    }


    public function rules()
    {
        return [
            [['amount'], 'double', 'min' => 0.01, 'tooSmall' => 'Refund amount should be greater than 0'],
            [['amount'], 'toCentsValidator'],
            [['transactionId'], 'integer'],
            [['errors'], 'emptyTransactionModelValidator', 'skipOnEmpty' => false],
            [['errors'], 'canTransactionBeRefundedValidator', 'skipOnEmpty' => false],
            [['errors'], 'isAllowedToProcessBankTransaction', 'skipOnEmpty' => false],
        ];
    }

    public function emptyTransactionModelValidator()
    {
        if (empty($this->transactionModel)) {
            $this->addError('', 'Transaction not found.');
        }
    }

    public function toCentsValidator()
    {
        $this->amount = Transaction::amountToCents($this->amount);
    }

    public function canTransactionBeRefundedValidator()
    {
        /**
         * @var Transaction $transaction
         */
        $transaction = $this->transactionModel;
        if (empty($transaction) || !$transaction->isAllowedRefund()) {
            $this->addError('', 'This transaction can not be refunded');
        }
    }

    public function isAllowedToProcessBankTransaction()
    {
        //refund of bank transaction available only for group charges
        if (empty($this->transactionModel) || ($this->transactionModel->isBankTransaction() && !$this->processGroupTransaction)) {
            $this->addError('', 'Refund could not be processed for bank account transfers. Please contact us for more information');
        }
    }

    public function refund($validate = true): bool
    {
        if ($validate && !$this->validate()) {
            return false;
        }
        try {
            /**
             * @var Transaction $model
             */
            $model = $this->transactionModel;

            if (((double)$model->amount === (double)$this->amount) && $model->isHasPartialRefunds()) {
                $this->amount = $this->calculateNotRefundedSum();
            }
            //do not process client-balance transactions as Full Refund only as partial
            if ($model->isClientBalance() && empty($this->amount)) {
                $this->amount = Transaction::amountToCents($model->calculateNotRefundedSum());
            }

            if ($model->isLessonBatchPayment() || $model->isLessonTransfer()) {
                return $this->processLessonTransferRefund($model);
            }

            if ($model->status !== Transaction::STATUS_NEW) {
                $refund = Yii::$app->payment->refund($model, $this->amount);
                if (!in_array($refund->status, [Module::REFUND_STATUS_SUCCEED, Module::REFUND_STATUS_PENDING])) {
                    throw new \Exception('Refund failed');
                }
                //do not change type for group transaction
            } elseif (!$model->isGroupChargeTransaction()) {
                $this->saveRefundedType($this->transactionModel);
            }
        } catch (\Throwable $exception) {
            Transaction::logTransactionError($this->transactionModel, $exception, 'payment');
            $this->addError('', $exception->getMessage());
            return false;
        }
        return true;
    }

    /**
     * @param Transaction $model
     */
    protected function processLessonTransferRefund($model): bool
    {
        //revers lesson transfer
        //There is no status field in response. In case of error should be exception
        $reversTransferData = Yii::$app->payment->reversTransfer($model->transactionExternalId);

        //lesson create during batch payment process
        if ($model->isLessonBatchPayment()) {
            //save refunded lesson id into storage in payment component
            Yii::$app->payment->refundLesson = $model->lesson;
            $groupTransaction = $model->groupTransaction;
            if (empty($groupTransaction)) {
                throw new \Exception('Transfer without group charge.');
            }

            //partial refund of groupTransaction
            $refundData = new static();
            $refundData->transactionId = $groupTransaction->id;
            //return to company full sum of lesson with platform commission
            $refundData->amount = ($model->amount + $model->fee);
            $refundData->processGroupTransaction = true;
            $this->saveRefundedType($model);
            return $refundData->refund();
        //lesson created during platform account payment process
        } elseif ($model->isLesson()) {
            $lesson = $model->lesson;
            if ($model->isLessonTransfer()) {
                /**
                 * @var Formatter $formatter
                 */
                $formatter = \Yii::$app->formatter;

                $parentId = $model->id;
                //create new transaction to store revers transfer data separately.
                // Also in this case client-balance-transaction of lesson and lesson-refund
                // will be related to different rows in `transaction` table
                $newTransaction = clone($model);
                $newTransaction->id = null;
                $newTransaction->isNewRecord = true;
                $newTransaction->parentId = $parentId;
                $newTransaction->refresh();
                $newTransaction->type = Transaction::STRIPE_REFUND;
                $newTransaction->amount = $model->amount;
                $newTransaction->createdAt = date('Y-m-d');
                $newTransaction->objectType = Transaction::TYPE_LESSON;
                $newTransaction->objectId = $model->objectId;
                $newTransaction->studentId = $lesson->studentId;
                $newTransaction->tutorId = $lesson->tutorId;
                $newTransaction->transactionExternalId = $reversTransferData->id;
                $newTransaction->response = $reversTransferData;
                $newTransaction->processDate = date($formatter->MYSQL_DATE);
                $newTransaction->status = Transaction::STATUS_SUCCESS;
                $newTransaction->refundInitiator = Yii::$app->user->id;
                $newTransaction->createdAt = date($formatter->MYSQL_DATETIME);
                $newTransaction->save(false);
            }

            $lesson->saveRefundedStatus();
            //if user is company client - add funds to balance
            Module::createRefundClientBalance($newTransaction ?? $model);
        }

        $this->saveRefundedType($model);
        return true;
    }

    /**
     * @param $transaction
     * @param bool $runValidation
     * @return mixed
     */
    protected function saveRefundedType($transaction, $runValidation = false)
    {
        $transaction->type = Transaction::STRIPE_REFUND;
        return $transaction->save($runValidation);
    }
}

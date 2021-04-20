<?php

namespace modules\payment\models\query;

use common\components\ActiveQuery;
use modules\account\models\Account;
use yii\db\Expression;
use modules\payment\models\Transaction;
use Yii;

/**
 * Class TransactionQuery
 * @package modules\payment\models\query
 * @property string $tableName
 */
class TransactionQuery extends ActiveQuery
{

    /**
     * @return $this
     */
    public function byReadyForTransfer()
    {
        return $this->andWhere([
            'and',
            ['=', 'transaction.status', Transaction::STATUS_SUCCESS],
            ['=', 'transaction.type', Transaction::STRIPE_CHARGE],
            ['=', 'transaction.objectType', Transaction::TYPE_LESSON],
            ['transaction.bankTransactionId' => null],
            ['<=', 'transaction.processDate', date('Y-m-d', time())],
        ]);
    }

    public function lessonTransfer()
    {
        return $this->andWhere(['objectType' => Transaction::TYPE_LESSON])->andWhere(['type' => Transaction::STRIPE_TRANSFER]);
    }


    /**
     * add by status condition
     * @param $status
     * @return $this
     */
    public function byStatus($status)
    {
        return $this->andWhere([$this->tableName . '.status' => $status]);
    }

    public function notTransfers()
    {
        return $this->andWhere(['not', [$this->tableName . '.type' => Transaction::STRIPE_TRANSFER]]);
    }

    /**
     * @return TransactionQuery
     */
    public function excludeRefundOfLessonTransfers(): self
    {
        return $this->andWhere(['not', [
            'and',
            ['like', 'transactionExternalId', 'trr%', false],
            ['objectType' => Transaction::TYPE_LESSON],
            ['type' => Transaction::STRIPE_REFUND]
        ]
        ]);
    }

    public function byCreatedAt(string $condition, string $date)
    {
        return $this->andWhere([$condition, $this->tableName . '.createdAt', $date]);
    }



    /**
     * add by student condition
     * @param $student
     * @return $this
     */
    public function byStudent(Account $student)
    {
        return $this->andWhere([$this->tableName . '.studentId' => $student->id]);
    }

    /**
     * find by active student transactions
     * @param Account $student
     * @return $this
     */
    public function byActiveStudentTransactions(Account $student)
    {
        return $this
            ->byStudent($student)
            ->byInStatuses([Transaction::STATUS_NEW, Transaction::STATUS_WAITING_FOR_APPROVE]);
    }



    /**
     * add by in statuses condition
     * @param array $statuses
     * @return $this
     */
    public function byInStatuses(array $statuses)
    {
        return $this->andWhere(['in', $this->tableName . '.status', $statuses]);
    }

    /**
     * add by object type condition
     * @param $type
     * @return $this
     */
    public function byObjectType($type)
    {
        return $this->andWhere([$this->tableName . '.objectType' => Transaction::TYPE_LESSON]);
    }

    /**
     * add by tutor condition
     * @param Account $tutor
     * @return $this
     */
    public function byTutor(Account $tutor)
    {
        return $this->andWhere([$this->tableName . '.tutorId' => $tutor->id]);
    }

    /**
     * add by process date less then now condition
     * @param $type
     * @return $this
     */
    public function byProcessDateLessThanNow()
    {
        return $this->andWhere(['<=', $this->tableName . '.processDate', new Expression('NOW()')]);
    }

    /**
     * @return $this
     */
    public function joinWithActiveStudent()
    {
        return $this->joinWith(['student' => function ($studentQuery) {
            return $studentQuery->byActiveStatus();
        }
        ]);
    }

    /**
     * @param Account $tutor
     * @return $this
     */
    public function byLastTutorTransfer(Account $tutor)
    {
        return $this->byTutor($tutor)->byTypeTransfer()->orderByLast();
    }

    /**
     * @return $this
     */
    public function byTypeTransfer()
    {
        return $this->byType(Transaction::STRIPE_TRANSFER);
    }

    /**
     * @param $type
     * @return $this
     */
    public function byType($type)
    {
        return $this->andWhere([$this->tableName . '.type' => $type]);
    }

    /**
     * @return $this
     */
    public function orderByLast()
    {
        return $this->orderBy([$this->tableName . '.id' => SORT_DESC]);
    }

    /**
     * @return $this
     */
    public function waitingForApprove()
    {
        return $this->andWhere([$this->tableName . '.status' => Transaction::STATUS_WAITING_FOR_APPROVE]);
    }

    /**
     * add by new transactions lessons condition
     * @return $this
     */
    public function byNewLessonTransactions()
    {
        return $this
           ->joinWithActiveStudent()
           ->byStatus(Transaction::STATUS_NEW)
           ->byObjectType(Transaction::TYPE_LESSON)
           ->byProcessDateLessThanNow();
    }

    /**
     * add by new transactions lessons condition
     * @return $this
     */
    public function byNewTransactions()
    {
        return $this
           ->joinWithActiveStudent()
           ->byStatus(Transaction::STATUS_NEW)
           ->byProcessDateLessThanNow();
    }

    /**
     * For lessons and earnings
     * @todo: this is copypast from controller. This logic needs serious refactor
     * @return $this
     */
    public function byTransactionsHistory()
    {
        return $this->andWhere([
            'or',
            [
                'and',
                ['transaction.status' => Transaction::STATUS_NEW],
                ['transaction.type' => Transaction::STRIPE_CAPTURE]
            ],
            [
                'and',
                ['transaction.status' => Transaction::STATUS_SUCCESS],
                ['in', 'transaction.type',  [ Transaction::STRIPE_TRANSFER, Transaction::STRIPE_CAPTURE]]
            ],
            [
                'and',
                [
                    'in',
                    'transaction.status',
                    [
                        Transaction::STATUS_SUCCESS,
                        Transaction::STATUS_NEW,
                        Transaction::STATUS_WAITING_FOR_APPROVE,
                        Transaction::STATUS_REJECTED,
                        Transaction::STATUS_PENDING,
                    ]
                ],
                [
                    'transaction.billingCycleStatus' => Transaction::NEW_BILLING_CYCLE_STATUS,
                ],

            ],
        ]);
    }


    public function clientBalance()
    {
        return $this->andWhere(['or', ['objectType' => Transaction::TYPE_CLIENT_BALANCE_AUTO], ['objectType' => Transaction::TYPE_CLIENT_BALANCE_MANUAL_CHARGE], ['objectType' => Transaction::TYPE_CLIENT_BALANCE_POST_PAYMENT]]);
    }

    public function child($id)
    {
        return $this->andWhere(['parentId' => $id]);
    }

    public function statusNotError()
    {
        return $this->andWhere(['<>','status', Transaction::STATUS_ERROR]);
    }

    public function partialRefundsOf($id)
    {
        return $this->andWhere(['parentId' => $id]);
    }

    public function lessonBatchPayment()
    {
        return $this->andWhere(['objectType' => Transaction::TYPE_LESSON_BATCH_PAYMENT]);
    }

    public function ofStudent($id)
    {
        return $this->andWhere([Transaction::tableName() . '.studentId' => $id]);
    }

    public function whereStatusPending()
    {
        return $this->byStatus(Transaction::STATUS_PENDING);
    }

    public function whereStatusError()
    {
        return $this->byStatus(Transaction::STATUS_ERROR);
    }

    public function whereGroupTransactionId($id)
    {
        return $this->andWhere(['groupTransactionId' => $id]);
    }

    public function groupChargeTransaction()
    {
        return $this->andWhere(['objectType' => Transaction::TYPE_COMPANY_GROUP_PAYMENT]);
    }

    public function whereExternalTransactionId($id)
    {
        return $this->andWhere(['transactionExternalId' => $id]);
    }

    /**
     * for groupChargeTransaction object id it is company id
     * @param $id
     * @return TransactionQuery
     */
    public function ofCompany($id)
    {
        return $this->andWhere(['objectId' => $id]);
    }
}

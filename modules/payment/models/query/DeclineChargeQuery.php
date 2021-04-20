<?php

namespace modules\payment\models\query;

use common\components\ActiveQuery;
use modules\account\models\Account;
use modules\payment\models\DeclineCharge;
use modules\payment\models\Transaction;

/**
 * This is the ActiveQuery class for [[\modules\payment\models\DeclineCharge]].
 *
 * @see \modules\payment\models\DeclineCharge
 */
class DeclineChargeQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return \modules\payment\models\DeclineCharge[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \modules\payment\models\DeclineCharge|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * by student
     * @param Account $student
     * @return $this
     */
    public function byStudent(Account $student)
    {
        return $this->andWhere([$this->tableName . '.studentId' => $student->id ]);
    }

    /**
     * by tutor
     * @param Account $tutor
     * @return $this
     */
    public function byTutor(Account $tutor)
    {
        return $this->andWhere([$this->tableName . '.tutorId' => $tutor->id ]);
    }

    /**
     * by declined charge
     * @param Transaction $charge
     * @return $this
     */
    public function byCharge(Transaction $charge)
    {
        return $this->andWhere([$this->tableName . '.chargeId' => $charge->id]);
    }

    /**
     * except charge
     * @param Transaction $charge
     * @return $this
     */
    public function exceptCharge(Transaction $charge)
    {
        return $this->andFilterCompare($this->tableName . '.chargeId', $charge->id, '<>');
    }

    /**
     * declined today
     * @return $this
     */
    public function declinedToday()
    {
        return $this->andFilterCompare(
            $this->tableName . '.declineTime',
            (time() - DeclineCharge::ONE_DAY_IN_SECONDS),
            '>'
        );
    }

    /**
     * Add order by last
     * @return $this
     */
    public function orderLast()
    {
        return $this->addOrderBy([$this->tableName . '.declineTime' => SORT_DESC]);
    }
}

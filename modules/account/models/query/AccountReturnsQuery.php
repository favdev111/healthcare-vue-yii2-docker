<?php

namespace modules\account\models\query;

use modules\account\models\AccountReturn;

/**
 * This is the ActiveQuery class for [[\modules\account\models\AccountReturns]].
 *
 * @see \modules\account\models\AccountReturn
 */
class AccountReturnsQuery extends \yii\db\ActiveQuery
{
    public function refunds(): self
    {
        return $this->andWhere(['type' => AccountReturn::TYPE_REFUND]);
    }

    public function rematch(): self
    {
        return $this->andWhere(['type' => AccountReturn::TYPE_REMATCH]);
    }

    public function byType(int $type): self
    {
        return $this->andWhere(['type' => $type]);
    }

    public function byReasonCode(int $code): self
    {
        return $this->andWhere(['reasonCode' => $code]);
    }

    public function createdBy(int $id)
    {
        return $this->andWhere(['createdBy' => $id]);
    }

    public function byEmployeeId(int $id): self
    {
        return $this->andWhere(['employeeId' => $id]);
    }

    public function byJobHireId($id)
    {
        return $this->andWhere(['jobHireId' => $id]);
    }
}

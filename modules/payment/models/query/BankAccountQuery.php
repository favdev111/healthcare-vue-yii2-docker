<?php

namespace modules\payment\models\query;

use common\components\ActiveQuery;
use modules\payment\models\BankAccount;

/**
 * This is the ActiveQuery class for [[\modules\payment\models\BankAccount]].
 *
 * @see \modules\payment\models\BankAccount
 */
class BankAccountQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return \modules\payment\models\BankAccount[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \modules\payment\models\BankAccount|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @return $this
     */
    public function byActive()
    {
        return $this->andWhere([$this->tableName . ".active" => BankAccount::BANK_ACCOUNT_ACTIVE_TRUE]);
    }

    /**
     * @return $this
     */
    public function byVerified()
    {
        return $this->andWhere([$this->tableName . ".verified" => BankAccount::BANK_ACCOUNT_VERIFIED_TRUE]);
    }
}

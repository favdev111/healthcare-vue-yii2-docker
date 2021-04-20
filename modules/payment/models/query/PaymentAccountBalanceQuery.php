<?php

namespace modules\payment\models\query;

use common\components\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\modules\payment\models\PaymentAccountBalance]].
 *
 * @see \modules\payment\models\PaymentAccountBalance
 */
class PaymentAccountBalanceQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \modules\payment\models\PaymentAccountBalance[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \modules\payment\models\PaymentAccountBalance|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}

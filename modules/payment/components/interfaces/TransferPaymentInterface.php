<?php

namespace modules\payment\components\interfaces;

use modules\account\models\Account;

interface TransferPaymentInterface
{
    /**
     * @param $amount
     * @param $account
     * @param $sourceType
     * @return mixed
     */
    public function transferToBank($amount, $account, $sourceType);

    /**
     * @param $account
     * @return mixed
     */
    public function getBalance($account);
}

<?php

namespace modules\payment\components\interfaces;

use modules\account\models\Account;

interface ChargePaymentInterface
{

    /**
     * Charge payment
     * @param $capture
     * @param $customer
     * @param $destination
     * @param $amount
     * @param $applicationFee
     * @param $description
     * @return mixed
     */
    public function charge($capture, $customer, $destination, $amount, $applicationFee, $description);

    /**
     * Capture payment
     * @param $transaction
     * @return mixed
     */
    public function capture($transaction);

    /**
     * Remove card
     * @param $id
     * @param Account $account
     * @return boolean
     */
    public function removeCard($id, Account $account);
}

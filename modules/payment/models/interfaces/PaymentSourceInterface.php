<?php

namespace modules\payment\models\interfaces;

use modules\payment\models\PaymentCustomer;
use yii\db\ActiveQuery;

/**
 * Interface PaymentSourceInterface
 *
 * @property PaymentCustomer $paymentCustomer
 *
 * @package modules\payment\models\interfaces
 */
interface PaymentSourceInterface
{
    /**
     * @return ActiveQuery
     */
    public function getPaymentCustomer();

    /**
     * Textual representation of source type
     *
     * @return string
     */
    public function getPaymentSourceTypeText();
}

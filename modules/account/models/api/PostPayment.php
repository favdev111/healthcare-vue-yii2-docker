<?php

namespace modules\account\models\api;

use modules\payment\models\Transaction;

class PostPayment extends \common\models\PostPayment
{
    public function fields()
    {
        $fields = parent::fields();
        $fields['amount'] = function () {
            return (float) $this->amount;
        };
        return $fields;
    }

    public function extraFields()
    {
        $extraFields = parent::extraFields();
        $extraFields['isAllowedReChargeTransaction'] = function () {
            /**
             * @var Transaction $transaction
             */
            $transaction = $this->getLastRelatedTransactionQuery()->one();
            if (empty($transaction)) {
                return false;
            }
            return $transaction->isAllowedReCharge();
        };
        $extraFields['transaction'] = function () {
            return $this->getLastRelatedTransactionQuery()->one();
        };
        return $extraFields;
    }
}

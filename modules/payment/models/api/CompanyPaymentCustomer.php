<?php

namespace modules\payment\models\api;

use modules\account\models\api\Account;
use Yii;
use yii\db\ActiveQueryInterface;

/**
 * @inheritdoc
 */
class CompanyPaymentCustomer extends PaymentCustomer
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['accountId' => 'id']);
    }
}

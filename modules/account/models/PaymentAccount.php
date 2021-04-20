<?php

namespace modules\account\models;

use modules\payment\models\BankAccount;
use modules\payment\models\PaymentAccountBalance;
use Yii;

/**
 * This is the model class for table "{{%payment_account}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property string $paymentAccountId
 * @property integer $verified
 * @property string $createdAt
 * @property string $updatedAt
 * @property Account $tutor
 * @property PaymentAccountBalance $paymentAccountBalance
 */
class PaymentAccount extends \modules\payment\models\Account
{
    // TODO: Check if this should be moved to base model
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['accountId', 'paymentAccountId', 'createdAt'], 'required'],
            [['accountId', 'verified'], 'integer'],
            [['createdAt', 'updatedAt'], 'safe'],
            [['paymentAccountId'], 'string', 'max' => 32],
        ];
    }
}

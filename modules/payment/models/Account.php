<?php

namespace modules\payment\models;

use modules\account\models\AccountWithDeleted;
use modules\payment\components\Payment;
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
 * @property array $capabilities - array [capabilityName => status]
 * @property bool $updatesRequired - has capability requirements
 *
 * @property \modules\account\models\Account $account
 */
class Account extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payment_account}}';
    }

    /**
     * @return null | double
     */
    public function getAvailableAmount()
    {
        $balanceModel = $this->paymentAccountBalance;
        if ($balanceModel) {
            return (double)$balanceModel->balance;
        }

        $balance = Yii::$app->payment->getBalance($this->paymentAccountId);

        if (empty($balance->available)) {
            return;
        }

        return Payment::fromStripeAmount($balance->available[0]->amount);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentAccountBalance()
    {
        return $this->hasOne(PaymentAccountBalance::class, ['paymentAccountId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTutor()
    {
        return $this->hasOne(AccountWithDeleted::class, ['id' => 'accountId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActiveBankAccount()
    {
        return $this->hasOne(BankAccount::class, ['paymentAccountId' => 'id'])->byActive();
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBankAccounts()
    {
        return $this->hasMany(BankAccount::class, ['paymentAccountId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(AccountWithDeleted::class, ['id' => 'accountId']);
    }

    /**
     * @return bool - returns false in at least one capability has requirements
     */
    public function checkCapabilities(): bool
    {
        foreach ($this->capabilities as $name => $value) {
            //check only in case capability is not active
            if ('active' != $value) {
                if (!Yii::$app->payment->checkAccountCapability($this->paymentAccountId, $name)) {
                    return false;
                }
            }
        }
        return true;
    }

    public function hasCapabilityLegacy(): bool
    {
        if (empty($this->capabilities)) {
            return false;
        }
        return in_array(Payment::CAPABILITY_LEGACY, array_keys($this->capabilities ?? []));
    }
}

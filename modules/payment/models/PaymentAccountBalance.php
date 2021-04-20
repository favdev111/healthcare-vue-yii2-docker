<?php

namespace modules\payment\models;

use common\components\ActiveRecord;
use modules\payment\models\query\PaymentAccountBalanceQuery;
use Yii;
use modules\account\models\PaymentAccount;

/**
 * This is the model class for table "{{%payment_account_balance}}".
 *
 * @property integer $id
 * @property integer $paymentAccountId
 * @property string $balance
 * @property integer $balanceNegativeNotified
 * @property integer $balanceRestoredNotified
 *
 * @property PaymentAccount $paymentAccount
 */
class PaymentAccountBalance extends ActiveRecord
{
    const STATUS_NOTIFIED_FALSE = 0;
    const STATUS_NOTIFIED_TRUE = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payment_account_balance}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['paymentAccountId', 'balanceRestoredNotified', 'balanceNegativeNotified'], 'integer'],
            [['balanceRestoredNotified', 'balanceNegativeNotified'], 'in', 'range' => [self::STATUS_NOTIFIED_FALSE, self::STATUS_NOTIFIED_TRUE]],
            [['paymentAccountId', 'balance'], 'required'],
            [['balance'], 'number'],
            [['paymentAccountId'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentAccount::className(), 'targetAttribute' => ['paymentAccountId' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'paymentAccountId' => Yii::t('app', 'Payment Account ID'),
            'balance' => Yii::t('app', 'Balance'),
            'isNotified' => Yii::t('app', 'Is Notified'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentAccount()
    {
        return $this->hasOne(PaymentAccount::className(), ['id' => 'paymentAccountId']);
    }

    /**
     * @return bool
     */
    public function isBalanceNegativeNotified()
    {
        return $this->balanceNegativeNotified === static::STATUS_NOTIFIED_TRUE;
    }

    /**
     * @return bool
     */
    public function isBalanceRestoredNotified()
    {
        return $this->balanceRestoredNotified === static::STATUS_NOTIFIED_TRUE;
    }

    /**
     * @return bool
     */
    public function updateBalanceRestoredNotified()
    {
        $this->setAttributes([
            'balanceRestoredNotified' => static::STATUS_NOTIFIED_TRUE,
            'balanceNegativeNotified' => static::STATUS_NOTIFIED_FALSE,
        ]);
        return $this->save();
    }

    /**
     * @return bool
     */
    public function updateBalanceNegativeNotified()
    {
        $this->setAttributes([
            'balanceNegativeNotified' => static::STATUS_NOTIFIED_TRUE,
            'balanceRestoredNotified' => static::STATUS_NOTIFIED_FALSE,
        ]);
        return $this->save();
    }

    /**
     * @return bool
     */
    public function isBalanceNegative()
    {
        return $this->balance < 0;
    }


    /**
     * @inheritdoc
     * @return \modules\payment\models\query\PaymentAccountBalanceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PaymentAccountBalanceQuery(static::class);
    }
}

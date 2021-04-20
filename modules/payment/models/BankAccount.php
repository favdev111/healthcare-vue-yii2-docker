<?php

namespace modules\payment\models;

use modules\account\models\PaymentAccount;
use modules\payment\models\query\BankAccountQuery;
use Stripe\BankAccount as StripeBankAccount;
use Yii;

/**
 * This is the model class for table "{{%bank_account}}".
 *
 * @property integer $id
 * @property integer $paymentAccountId
 * @property string $paymentBankId
 * @property integer $verified
 * @property string $createdAt
 * @property string $updatedAt
 * @property integer $active
 *
 * @property PaymentAccount $paymentAccount
 */
class BankAccount extends \yii\db\ActiveRecord
{

    const BANK_ACCOUNT_VERIFIED_TRUE = 1;
    const BANK_ACCOUNT_VERIFIED_FASLE = 0;

    const BANK_ACCOUNT_ACTIVE_TRUE = 1;
    const BANK_ACCOUNT_ACTIVE_FASLE = 0;

    protected $stripeBankAccount;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bank_account}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['paymentAccountId'], 'required'],
            [['paymentAccountId', 'verified', 'active'], 'integer'],
            ['active', 'in', 'range' => [self::BANK_ACCOUNT_ACTIVE_FASLE, self::BANK_ACCOUNT_ACTIVE_TRUE]],
            ['verified', 'in', 'range' => [self::BANK_ACCOUNT_VERIFIED_FASLE, self::BANK_ACCOUNT_VERIFIED_TRUE]],
            [['createdAt', 'updatedAt'], 'safe'],
            [['paymentBankId'], 'string', 'max' => 32],
            [['paymentAccountId'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentAccount::className(), 'targetAttribute' => ['paymentAccountId' => 'id']],
        ];
    }

    /**
     * @param Account $account
     * @return StripeBankAccount | null
     * @throws \Exception
     */
    public function getStripeBankAccount($account = null)
    {
        if (!$this->stripeBankAccount) {
            $this->stripeBankAccount = Yii::$app->payment->getBankAccount($this->paymentBankId, $account);
        }
        return $this->stripeBankAccount;
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (!$this->getStripeBankAccount($this->paymentAccount->account)->delete()) {
            return false;
        }
        return parent::beforeDelete();
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->getStripeBankAccount()->default_for_currency === true;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'paymentAccountId' => Yii::t('app', 'Payment Account ID'),
            'paymentBankId' => Yii::t('app', 'Payment Bank ID'),
            'verified' => Yii::t('app', 'Verified'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'active' => Yii::t('app', 'Active'),
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
     * @inheritdoc
     * @return \modules\payment\models\query\BankAccountQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new BankAccountQuery(static::class);
    }
}

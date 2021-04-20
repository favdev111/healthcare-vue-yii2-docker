<?php

namespace modules\payment\models;

use modules\payment\models\query\PaymentCustomerQuery;
use modules\payment\Module;
use Yii;
use modules\account\models\Account;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%payment_customer}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property string $customerId
 * @property string $address @deprecated
 * @property string $zipcode @deprecated
 * @property string $apartment @deprecated
 * @property boolean $autorenew
 * @property integer $packagePrice
 * @property string $updatedAt
 *
 * @property Account $account
 * @property CardInfo $activeCard
 * @property PaymentBankAccount $activeBankAccount
 * @property CardInfo[] $cardInfo
 * @property PaymentBankAccount[] $bankAccounts
 * @property PaymentBankAccount|CardInfo $activeCardOrBankAccount
 */
class PaymentCustomer extends \yii\db\ActiveRecord
{
    public $newActiveCardId;
    public $triggerPackageCharge;

    /**
     * @return PaymentCustomerQuery
     */
    public static function find()
    {
        return new PaymentCustomerQuery(static::class);
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payment_customer}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
                'createdAtAttribute' => false,
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['accountId'], 'required'],
            [['accountId', 'triggerPackageCharge'], 'integer'],
            [['packagePrice'], 'double', 'min' => 10],
            ['autorenew', 'boolean'],
            'autorenew_default' => ['autorenew', 'default', 'value' => true],
            [['customerId'], 'string', 'max' => 255],
            [
                'newActiveCardId',
                'exist',
                'skipOnEmpty' => true,
                'skipOnError' => true,
                'targetClass' => CardInfo::className(),
                'targetAttribute' => 'id',
                'filter' => function ($query) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query->andWhere(['stripeCustomerId' => $this->id]);
                },
            ],
        ];
    }

    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'accountId']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'accountId' => 'Account ID',
            'customerId' => 'Customer ID',
        ];
    }

    public function getCardInfo()
    {
        return $this->hasMany(CardInfo::className(), ['stripeCustomerId' => 'id']);
    }

    public function getBankAccounts()
    {
        return $this->hasMany(PaymentBankAccount::className(), ['paymentCustomerId' => 'id']);
    }

    public function getActiveCard()
    {
        return $this->hasOne(CardInfo::className(), ['stripeCustomerId' => 'id'])->andOnCondition(['active' => true])->limit(1);
    }

    public function getActiveBankAccount()
    {
        return $this->hasOne(PaymentBankAccount::className(), ['paymentCustomerId' => 'id'])->andOnCondition(['active' => true])->limit(1);
    }

    public function getActiveCardOrBankAccount()
    {
        return $this->activeCard ?? $this->activeBankAccount;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($this->newActiveCardId) {
            /**
             * @var $model CardInfo
             */
            $model = $this->getCardInfo()->andWhere(['id' => $this->newActiveCardId])->one();
            if ($model) {
                $model->active = true;
                if (!$model->save(['active'])) {
                    $this->addError('newActiveCardId', $model->getFirstError('active'));
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function isPAYG(): bool
    {
        return empty($this->packagePrice);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->triggerPackageCharge && $this->packagePrice) {
            /**
             * @var $paymentModule Module
             */
            $paymentModule = Yii::$app->getModule('payment');
            $paymentModule->processClientTransaction($this->account, $this->triggerPackageCharge ? $this->packagePrice : null, $this->triggerPackageCharge);
        }
    }
}

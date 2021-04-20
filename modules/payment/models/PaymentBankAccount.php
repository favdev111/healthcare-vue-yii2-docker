<?php

namespace modules\payment\models;

use modules\payment\models\interfaces\PaymentSourceInterface;
use modules\payment\Module;
use Yii;

/**
 * This is the model class for table "{{%payment_bank_account}}".
 *
 * @property integer $id
 * @property integer $paymentCustomerId
 * @property string $paymentBankId
 * @property integer $verified
 * @property string $bank_name
 * @property string $last4
 * @property string $createdAt
 * @property string $updatedAt
 * @property integer $active
 *
 * @property PaymentCustomer $paymentCustomer
 */
class PaymentBankAccount extends \yii\db\ActiveRecord implements PaymentSourceInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payment_bank_account}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['paymentCustomerId'], 'required'],
            [['paymentCustomerId'], 'integer'],
            [['active', 'verified'], 'boolean'],
            ['verified', 'default', 'value' => false],
            [['paymentBankId', 'bank_name'], 'string', 'max' => 32],
            [['last4'], 'string', 'max' => 4],
            [['paymentCustomerId'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentCustomer::className(), 'targetAttribute' => ['paymentCustomerId' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'paymentCustomerId' => Yii::t('app', 'Payment Customer ID'),
            'paymentBankId' => Yii::t('app', 'Payment Bank ID'),
            'verified' => Yii::t('app', 'Verified'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'active' => Yii::t('app', 'Active'),
        ];
    }

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
    public function getPaymentCustomer()
    {
        return $this->hasOne(PaymentCustomer::className(), ['id' => 'paymentCustomerId']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (
            ($insert || isset($changedAttributes['active']))
            && $this->active
        ) {
            CardInfo::updateAll(['active' => false], ['stripeCustomerId' => $this->paymentCustomerId]);
            PaymentBankAccount::updateAll(
                ['active' => false],
                [
                    'and',
                    ['paymentCustomerId' => $this->paymentCustomerId],
                    ['not', ['id' => $this->id]],
                ]
            );
            Yii::$app->payment->setActiveCardOrBankAccountOnStripe(
                $this->paymentCustomer->customerId,
                $this->paymentBankId
            );

            /**
             * @var Module $paymentModule
             */
            $paymentModule = Yii::$app->getModule('payment');
            $paymentModule->eventNewActivePaymentBankAccountOrCard($this->paymentCustomer->account);
        }
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        if ($this->isActive()) {
            return false;
        }

        Yii::$app->payment->removeBankAccount($this);

        return true;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active === true;
    }

    public function getPaymentSourceTypeText()
    {
        return 'payment bank account';
    }
}

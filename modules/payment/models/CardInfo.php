<?php

namespace modules\payment\models;

use common\components\app\ConsoleApplication;
use modules\payment\models\interfaces\PaymentSourceInterface;
use modules\payment\models\query\CardInfoQuery;
use modules\payment\Module;
use Yii;
use common\components\HtmlPurifier;
use modules\account\models\Account as UserAccount;

/**
 * This is the model class for table "{{%payment_card_info}}".
 *
 * @property integer $id
 * @property string $cardNumber
 * @property string $brand
 * @property string $tokenCard
 * @property integer $month
 * @property integer $year
 * @property integer $active
 * @property integer $createdAt
 * @property integer $updatedAt
 * @property integer $stripeCustomerId
 * @property integer $cardId
 * @property string $holderName
 *
 * @property UserAccount $account
 * @property PaymentCustomer $paymentCustomer
 */
class CardInfo extends \yii\db\ActiveRecord implements PaymentSourceInterface
{
    public $fullName;
    public $cvv;

    const STATUS_ACTIVE = 1;
    const STATUS_NOT_ACTIVE = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payment_card_info}}';
    }

    /**
     * @return CardInfoQuery
     */
    public static function find()
    {
        return new CardInfoQuery(static::class);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active === self::STATUS_ACTIVE;
    }

    /**
     * Setter for active status
     */
    public function setStatusActive()
    {
        $this->active = static::STATUS_ACTIVE;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['cvv', 'fullName', 'cardNumber', 'brand'],
                function ($attribute) {
                    $this->$attribute = HtmlPurifier::process($this->$attribute, ['HTML.Allowed' => '']);
                }
            ],
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

    public function beforeSave($insert)
    {
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($this->active == 1) {
            if (!(Yii::$app instanceof ConsoleApplication)) {
                Yii::$app->session->removeFlash("error");
            }
        }

        /**
         * @var Module $paymentModule
         */
        $paymentModule = Yii::$app->getModule('payment');

        if ($insert) {
            $paymentModule->eventNewCard($this);
        }

        if (
            ($insert || isset($changedAttributes['active']))
            && $this->active
        ) {
            CardInfo::updateAll(
                ['active' => false],
                [
                    'and',
                    ['stripeCustomerId' => $this->stripeCustomerId],
                    ['not', ['id' => $this->id]],
                ]
            );

            if ($this->paymentCustomer->account->isCrmAdmin()) {
                $paymentModule->eventNewActivePaymentBankAccountOrCard($this->paymentCustomer->account);
            } else {
                $paymentModule->eventNewActiveCard($this->paymentCustomer->account);
            }

            PaymentBankAccount::updateAll(['active' => false], ['paymentCustomerId' => $this->stripeCustomerId]);
            Yii::$app->payment->setActiveCardOrBankAccountOnStripe(
                $this->paymentCustomer->customerId,
                $this->cardId
            );
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cardNumber' => 'Card Number',
        ];
    }

    /**
     * Is account owner
     * @param UserAccount $account
     * @return bool
     */
    public function isOwner(UserAccount $account)
    {
        $customer = PaymentCustomer::findOne(['accountId' => $account->id]);
        return $customer->accountId === $account->id;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'accountId'])
            ->viaTable(PaymentCustomer::tableName(), ['id' => 'stripeCustomerId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentCustomer()
    {
        return $this->hasOne(PaymentCustomer::className(), ['id' => 'stripeCustomerId']);
    }

    public function isAmex()
    {
        return in_array($this->brand, ['amex', 'american express']);
    }

    public function isDinnersClub()
    {
        return $this->brand == 'diners club';
    }

    public function getHumanCardNumber()
    {
        if ($this->isDinnersClub()) {
            return '**** **** **' . substr($this->cardNumber, 0, 2) . ' ' . substr($this->cardNumber, 2, 2);
        }
        if ($this->isAmex()) {
            return '**** ****** *' . $this->cardNumber;
        }
        return '**** **** **** ' . $this->cardNumber;
    }

    /**
     * Get human card number for mobile
     * @return string
     */
    public function getHumanCardNumberMobile()
    {
        return '**** ' . $this->cardNumber;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id',
            'brand',
            'cardNumber' => 'humanCardNumber',
            'month',
            'year',
            'holderName',
            'active' => function () {
                return (bool) $this->active;
            },
            'paymentCustomerId' => 'stripeCustomerId',
        ];
    }

    public function afterDelete()
    {
        parent::afterDelete();
        /**
         * @var Module $paymentModule
         */
        $paymentModule = Yii::$app->getModule('payment');
        $paymentModule->eventCardDeleted($this->paymentCustomer->account);
        return true;
    }

    public function getPaymentSourceTypeText()
    {
        return 'credit card';
    }
}

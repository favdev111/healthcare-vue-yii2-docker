<?php

namespace modules\account\models;

use modules\account\components\ChangeRateEvent;
use modules\account\helpers\EventHelper;
use Yii;

/**
 * This is the model class for table "{{%account_rate}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property integer $numberShown
 * @property integer $hourlyRate
 * @property integer $cancellationPolicy
 *
 * @property integer $displayRate
 * @property integer $fullRate
 * @property Account $account
 */
class Rate extends \yii\db\ActiveRecord
{
    const CANCELLATION_POLICY_NONE = 0;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_rate}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $module = Yii::$app->getModuleAccount();
        return [
            [['numberShown'], 'required', 'when' => function () {
                return $this->isNewRecord;
            }
            ],
            [['numberShown'], 'integer'],
            ['!cancellationPolicy', 'default', 'value' => static::CANCELLATION_POLICY_NONE],
            ['hourlyRate', 'required', 'on' => 'setRate'],
            ['hourlyRate', 'double', 'min' => $module->hourlyRateMin, 'max' => $module->hourlyRateMax]
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenario = parent::scenarios();
        $scenario['setRate'] = ['hourlyRate'];
        return $scenario;
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'accountId' => 'Account ID',
            'numberShown' => 'Number Shown',
            'hourlyRate' => 'Hourly Rate',
            'cancellationPolicy' => 'Cancellation Policy',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'accountId']);
    }

    public function getCancellationPolicyName()
    {
        $array = $this->cancellationPolicyArray;
        return $array[$this->cancellationPolicy];
    }

    public static function getCancellationPolicyArray()
    {
        return [
            0 => 'None',
            5 => '5 Hours',
            10 => '10 Hours',
            24 => '24 Hours',
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        if (
            !$insert
            && isset($changedAttributes['hourlyRate'])
        ) {
            EventHelper::changeHourlyRateEvent(
                $this->accountId,
                $this->hourlyRate,
                $changedAttributes['hourlyRate'],
                $this
            );
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Return tutor's display rate depending on who is asking
     * Tutor sees his flat rate
     * Student/Guest see
     * @param $account - need to be provided in call inside console applications
     * @return float|int
     */
    public function getDisplayRate(Account $account = null)
    {
        if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isTutor()) {
            // For tutor's we always show rate without any commission
            return (int)$this->hourlyRate;
        }

        return round(self::calculateFullRate($this->hourlyRate, $account), 0);
    }

    /**
     * Get full rate with 20% commissions
     *
     * @return float
     */
    public function getFullRate(Account $identity = null)
    {
        return round(self::calculateFullRate($this->hourlyRate, $identity), 0);
    }

    /**
     * Calculate full rate
     * @param $rate
     * @param $identity
     * @return float
     */
    public static function calculateFullRate($rate, Account $identity = null)
    {
        if (!$identity) {
            $identity = Yii::$app->user->identity;
        }

        //for company employees and admins use company account
        $identity = $identity->getProcessAccount();

        if (
            // For guests
            Yii::$app->user->isGuest
            // Non-companies
            || !$identity->isCrmAdmin()
        ) {
            // Show regular rate
            return self::calculateRegularCommissionRate($rate);
        }

        if ($identity->isZeroCommissionCompany()) {
            // for zero commission companies - return flat rate
            return $rate;
        }
        // show rate with commission (5%,15%) less than regular rate (+20%)
        $commission = $identity->commission;
        return self::calculateB2bRate($rate, $commission);
    }

    /**
     * Calculate regular rate with 20% commission
     * @param $rate
     * @return float
     */
    protected static function calculateRegularCommissionRate($rate)
    {
        return $rate * 1.25;
    }

    /**
     * Calculate rate with 5% discount from regular rate
     * @param $rate
     * @param $commission
     * @return float|int
     */
    public static function calculateB2bRate($rate, $commission)
    {
        $fullRate = self::calculateRegularCommissionRate($rate);
        // 5% discount
        $discount = $fullRate / 20;
        if ($commission === Account::COMMISSION_FIVE) {
            // 5% * 3 = 15%
            $discount = $discount * 3;
        }
        $fullRate -= $discount;
        return $fullRate;
    }
}

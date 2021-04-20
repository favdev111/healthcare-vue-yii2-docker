<?php

namespace modules\payment\models\api;

use Yii;
use yii\db\ActiveQueryInterface;

/**
 * @inheritdoc
 */
class PaymentBankAccount extends \modules\payment\models\PaymentBankAccount
{
    const SCENARIO_VERIFICATION = 'verification';

    // Deposit amounts required for verification
    public $deposit1;
    public $deposit2;

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['deposit1', 'deposit2'], 'required', 'on' => self::SCENARIO_VERIFICATION];
        $rules[] = [['deposit1', 'deposit2'], 'number'];
        $rules[] = [
            'paymentBankId',
            function () {
                if (self::find()->andWhere(['paymentCustomerId' => $this->paymentCustomerId])->count() >= 10) {
                    $this->addError('', 'You can not add more than 10 bank accounts');
                }
            },
            'when' => function () {
                return $this->isNewRecord;
            }
        ];
        return $rules;
    }

    /**
     * @param $query ActiveQueryInterface
     * @return mixed
     */
    protected static function addOwnCondition($query)
    {
        $paymentCustomer = Yii::$app->user->identity->paymentCustomer;
        return $query->andWhere([self::tableName() . '.paymentCustomerId' => ($paymentCustomer->id ?? null)]);
    }

    /**
     * @inheritdoc
     */
    public static function findOne($id)
    {
        $query = parent::findByCondition(['id' => $id]);
        static::addOwnCondition($query);
        return $query->one();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        $query = parent::find();
        static::addOwnCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findByCondition($condition)
    {
        $query = parent::find($condition);
        static::addOwnCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findBySql($sql, $params = [])
    {
        $query = parent::findBySql($sql, $params);
        static::addOwnCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findAll($condition)
    {
        $query = parent::findByCondition($condition);
        static::addOwnCondition($query);
        return $query->all();
    }

    public function verify($data, $formName = '')
    {
        $this->scenario = self::SCENARIO_VERIFICATION;
        $this->load($data, $formName);
        if (!$this->validate()) {
            return false;
        }

        if (!Yii::$app->payment->ach->verifyBankAccount($this->paymentCustomer->customerId, $this->paymentBankId, $this->deposit1, $this->deposit2, $error)) {
            $this->addError('', $error ?? 'Failed to verify your bank account. Please try again or contact us.');
            return false;
        }
        if (!$this->paymentCustomer->activeCardOrBankAccount) {
            $this->active = true;
        }

        $this->verified = true;
        return $this->save(true, ['verified', 'active']);
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['active'] = function () {
            return (bool) $this->active;
        };
        $fields['verified'] = function () {
            return (bool) $this->verified;
        };
        return $fields;
    }
}

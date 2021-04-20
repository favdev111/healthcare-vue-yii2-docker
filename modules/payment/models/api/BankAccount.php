<?php

namespace modules\payment\models\api;

use modules\account\models\backend\Account;
use Yii;
use yii\db\ActiveQueryInterface;

/**
 * @inheritdoc
 */
class BankAccount extends \modules\payment\models\BankAccount
{
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    'active',
                    function () {
                        return $this->active == true;
                    },
                    'message' => 'Active card is not set.'
                ],
                [
                    'paymentBankId',
                    function () {
                        if (self::find()->andWhere(['paymentAccountId' => $this->paymentAccountId])->count() >= 10) {
                            $this->addError('', 'You can not add more than 10 bank accounts');
                        }
                    },
                    'when' => function () {
                        return $this->isNewRecord;
                    }
                ]
            ]
        );
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['stripeData'] = function () {
            $bankAccount = $this->getStripeBankAccount($this->paymentAccount->account);
            return [
                'bank_name' => $bankAccount->bank_name,
                'last4' => $bankAccount->last4,
            ];
        };
        $fields['active'] = function () {
            return (bool)$this->active;
        };
        return $fields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'accountId']);
    }

    /**
     * @param $query ActiveQueryInterface
     * @return mixed
     */
    protected static function addOwnCondition($query)
    {
        $paymentAccount = Yii::$app->user->identity->paymentAccount;
        if (!$paymentAccount) {
            return $query->where('0=1');
        }

        return $query->andWhere([self::tableName() . '.paymentAccountId' => $paymentAccount->id]);
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

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (!$insert && $this->active && $this->isAttributeChanged('active')) {
            Yii::$app->payment->updateDefaultBankAccount($this->paymentBankId, $this->paymentAccount->account);
            BankAccount::updateAll(
                ['active' => false],
                [
                    'and',
                    ['not', ['id' => $this->id]],
                    ['paymentAccountId' => $this->paymentAccountId],
                ]
            );
        }

        return true;
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        if ($this->active) {
            // It is prohibited to delete active bank account
            return false;
        }

        return true;
    }
}

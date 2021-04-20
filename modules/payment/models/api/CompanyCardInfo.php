<?php

namespace modules\payment\models\api;

use modules\account\models\api\Account;
use modules\payment\models\PaymentCustomer;
use Yii;
use yii\db\ActiveQueryInterface;

/**
 * @inheritdoc
 */
class CompanyCardInfo extends \modules\payment\models\CardInfo
{
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [
            'cardNumber',
            function () {
                if (self::find()->andWhere(['stripeCustomerId' => $this->stripeCustomerId])->count() >= 10) {
                    $this->addError('', 'You can not add more than 10 credit cards');
                }
            },
            'when' => function () {
                return $this->isNewRecord;
            }
        ];
        return $rules;
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
     * @param $query ActiveQueryInterface
     * @return mixed
     */
    protected static function addOwnCondition($query)
    {
        $companyId = Yii::$app->user->id;
        $ownClientPaymentCustomersQuery = PaymentCustomer::find()->andWhere([PaymentCustomer::tableName() . '.accountId' => $companyId])->select('id');
        return $query->andWhere([self::tableName() . '.stripeCustomerId' => $ownClientPaymentCustomersQuery]);
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
}

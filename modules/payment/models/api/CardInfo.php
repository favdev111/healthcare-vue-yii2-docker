<?php

namespace modules\payment\models\api;

use modules\account\models\api\AccountClient;
use yii\db\ActiveQueryInterface;

/**
 * @inheritdoc
 */
class CardInfo extends \modules\payment\models\CardInfo
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
        return $this->hasOne(AccountClient::className(), ['id' => 'accountId'])
            ->viaTable(PaymentCustomer::tableName(), ['id' => 'stripeCustomerId']);
    }

    /**
     * @inheritdoc
     */
    public static function findOne($id)
    {
        $query = parent::findByCondition(['id' => $id]);
        return $query->one();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        $query = parent::find();
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findByCondition($condition)
    {
        $query = parent::find($condition);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findBySql($sql, $params = [])
    {
        $query = parent::findBySql($sql, $params);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findAll($condition)
    {
        $query = parent::findByCondition($condition);
        return $query->all();
    }
}

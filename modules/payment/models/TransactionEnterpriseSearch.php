<?php

namespace modules\payment\models;

use modules\account\models\Role;
use modules\payment\models\backend\TransactionSearch;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use modules\payment\models\Transaction;

/**
 * TransactionEnterpriseSearch represents the model behind the search form about `modules\payment\models\Transaction`.
 */
class TransactionEnterpriseSearch extends TransactionSearch
{
    private $excludePartialRefund  = true;

    public function excludePartialRefund($value)
    {
        $this->excludePartialRefund = $value;
        return $this;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find();
        $query->joinWith('student st');
        $query->joinWith('tutor tut');

        $query->andWhere([ 'or',
            ['or',
                [
                    'and',
                    ['type' => static::STRIPE_TRANSFER],
                    ['tut.roleId' => Role::ROLE_SPECIALIST]
                ],
                ['and',
                    ['not', ['type' => static::STRIPE_TRANSFER]],
                ],

            ],
            ['objectType' => Transaction::TYPE_COMPANY_GROUP_PAYMENT]
        ]);

        $query->excludeRefundOfLessonTransfers();

        //not show partial refunds (except partial refunds of group payment - it is with object type lesson)
        if ($this->excludePartialRefund) {
            $query->andWhere(['not', ['type' => Transaction::PARTIAL_REFUND]]);
        }


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ],
            ],
        ]);

        $dataProvider->sort->attributes['studentEmail'] = [
            'asc' => ['st.email' => SORT_ASC],
            'desc' => ['st.email' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['tutorEmail'] = [
            'asc' => ['tut.email' => SORT_ASC],
            'desc' => ['tut.email' => SORT_DESC],
        ];

        /**
         * load method for form load data. setAttributes without form usage
         */
        $this->load($params) || $this->setAttributes($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->andFilterWhere([
            self::tableName() . '.id' => $this->id,
            'objectId' => $this->objectId,
            'transactionExternalId' => $this->transactionExternalId,
            'parentId' => $this->parentId,
            self::tableName() . '.status' => $this->status,
            'type' => $this->type,
            'bankTransactionId' => $this->bankTransactionId,
            'amount' => $this->amount,
            'fee' => $this->fee,
        ]);

        $query->andFilterWhere(['like', 'st.email', $this->studentEmail]);
        $query->andFilterWhere(['like', 'tut.email', $this->tutorEmail]);

        $query->andFilterWhere(['between', 'processDate', $this->processDateStart, $this->processDateEnd]);
        $query->andFilterWhere(['between', self::tableName() . '.createdAt', $this->createdAtDateStart, $this->createdAtDateEnd]);

        return $dataProvider;
    }
}

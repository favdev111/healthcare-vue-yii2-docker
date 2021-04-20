<?php

namespace modules\payment\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use modules\payment\models\Transaction;
use yii\db\ActiveQuery;

/**
 * TransactionSearch represents the model behind the search form about `modules\payment\models\Transaction`.
 */
class TransactionSearch extends Transaction
{
    const MOBILE_PAGE_SIZE = 4;
    const TRANSACTIONS_LIMIT_DESKTOP = 7;
    const TRANSACTIONS_LIMIT_MOBILE = 3;

    public $dateTo;
    public $dateFrom;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['processDate', 'dateTo', 'dateFrom'], 'safe'],
            ['dateFrom', 'date', 'type' => 'datetime', 'format' => 'php:m/d/Y', 'timestampAttribute' => 'dateFrom', 'timestampAttributeFormat' => 'php:Y-m-d'],
            ['dateTo', 'date', 'type' => 'datetime', 'format' => 'php:m/d/Y', 'timestampAttribute' => 'dateTo', 'timestampAttributeFormat' => 'php:Y-m-d'],
        ];
    }

    /**
     * @return $this
     */
    public function addDefaultDateRangeCondition()
    {
        if (!$this->dateFrom) {
            $this->fillDefaultFrom();
        }
        if (!$this->dateTo) {
            $this->fillDefaultTo();
        }
        return $this;
    }

    protected function fillDefaultFrom()
    {
        $this->dateFrom = date('Y-m-01');
    }

    protected function fillDefaultTo()
    {
        $this->dateTo = date('Y-m-d');
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Search for mobile
     *
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchMobile($params)
    {
        $dataProvider = $this->search($params);

        $dataProvider->pagination->setPageSize(self::MOBILE_PAGE_SIZE);

        return $dataProvider;
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
        /**
         * @var $account Account
         */
        $account = Yii::$app->user->identity;
        $this->load($params, '');
        if (!$this->validate()) {
            $this->fillDefaultFrom();
            $this->fillDefaultTo();
        }
        $transactions = Transaction::find()->joinWith(['lesson' => function ($query) use ($account) {
            /**
             * @var $query ActiveQuery
             */
            $query->andWhere(['lesson.studentId' => $account->id]);
        }
        ])
            ->andWhere(['transaction.objectType' => Transaction::TYPE_LESSON])
            ->andWhere([
                'or',
                [
                    'and',
                    ['transaction.status' => Transaction::STATUS_SUCCESS],
                    ['in', 'transaction.type',  [ Transaction::STRIPE_CHARGE, Transaction::STRIPE_REFUND, Transaction::STRIPE_CAPTURE ]],
                ],
                [
                    'and',
                    ['transaction.status' => Transaction::STATUS_NEW],
                    ['transaction.type' => Transaction::STRIPE_CAPTURE],
                ],
            ]);

        $transactions->andFilterWhere([
            'and',
            ['>=', 'transaction.processDate', $this->dateFrom],
            ['<=', 'transaction.processDate', $this->dateTo],
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $transactions,
            'pagination' => [
                'pageSize' => isset($params['per-page']) ? $params['per-page'] : 10,
                'params' => $params,
                'defaultPageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'processDate' => SORT_DESC,
                ],
            ],
        ]);

        return $dataProvider;
    }


    /**
     * @param $params array
     * @return ActiveQuery
     */
    protected function searchDashboard($params)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            $this->fillDefaultFrom();
            $this->fillDefaultTo();
        }
        /**
         * @var $account Account
         */
        $account = Yii::$app->user->identity;
        return self::find()->joinWith(['lesson' => function ($query) use ($account) {
            /**
             * @var $query ActiveQuery
             */
            $query->andWhere(['lesson.studentId' => $account->id]);
        }
        ])
            ->andWhere([
                'or',
                [
                    'and',
                    ['transaction.status' => self::STATUS_NEW],
                    ['transaction.type' => self::STRIPE_CAPTURE],
                ],
                [
                    'and',
                    ['transaction.status' => self::STATUS_SUCCESS],
                    ['in', 'transaction.type',  [ self::STRIPE_CHARGE, self::STRIPE_REFUND, self::STRIPE_CAPTURE ]],
                ],
            ])
            ->orderBy(['transaction.processDate' => SORT_DESC, 'transaction.id' => SORT_DESC]);
    }

    /**
     * @param ActiveQuery $query
     */
    protected function addDashboardFilter(ActiveQuery $query)
    {
        $query->andWhere(['transaction.objectType' => self::TYPE_LESSON]);
        $query->andFilterWhere([
            'and',
            ['>=', 'transaction.processDate', $this->dateFrom],
            ['<=', 'transaction.processDate', $this->dateTo],
        ]);
    }

    /**
     * @param $params array
     * @return ActiveQuery
     */
    public function searchDashboardDesktop($params)
    {
        $query = $this->searchDashboard($params)->limit(self::TRANSACTIONS_LIMIT_DESKTOP);
        $this->addDefaultDateRangeCondition();
        $this->addDashboardFilter($query);
        return $query;
    }

    /**
     * @param $params array
     * @return ActiveQuery
     */
    public function searchDashboardMobile($params)
    {
        $query = $this->searchDashboard($params)->limit(self::TRANSACTIONS_LIMIT_MOBILE);
        $this->addDefaultDateRangeCondition();
        $this->addDashboardFilter($query);
        return $query;
    }
}

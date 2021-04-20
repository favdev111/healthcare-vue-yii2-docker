<?php

namespace modules\account\models\search;

use common\components\behaviors\FilterDatesBehavior;
use modules\account\models\ClientBalanceTransaction;
use modules\account\models\Lesson;
use modules\payment\models\Transaction;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\Url;

class ClientBalanceTransactionSearch extends ClientBalanceTransaction
{
    protected $disablePagination = false;
    protected $withRelations = false;
    protected $defaultDateRanges = true;

    public function rules()
    {
        return array_merge([

        ], $this->getFilterDatesRulesArray());
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [FilterDatesBehavior::class]);
    }

    public function disablePagination($val)
    {
        $this->disablePagination = $val;
        return $this;
    }

    public function useDefaultDateRanges(bool $value): self
    {
        $this->defaultDateRanges = $value;
        return $this;
    }

    public function withRelations($value)
    {
        $this->withRelations = $value;
        return $this;
    }


    /**
     * @param $params
     * @param $pageSize
     * @param $includeHide - add rows with hide = 1 or not
     * @return ActiveDataProvider
     */
    public function search($params, $pageSize = 20, $includeHide = false)
    {
        // This method is used to search for own jobs. Showing all including suspended
        /**
         * @var ActiveQuery $query
         */
        $query = self::find();

        $providerParams = [
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'defaultPageSize' => $pageSize,
            ],
        ];
        if ($this->disablePagination) {
            $providerParams = array_merge($providerParams, ['pagination' => false]);
        }

        $dataProvider = new ActiveDataProvider($providerParams);

        $this->load($params, '');

        if ($this->defaultDateRanges) {
            $this->addDefaultDateRangeCondition();
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');
            return $dataProvider;
        }

        $query = $this->filterDate($query, static::tableName());

        $query->andFilterWhere([self::tableName() . '.clientId' => $this->clientId]);

        if (!$includeHide) {
            $query->andWhere(['hide' => 0]);
        }

        if ($this->withRelations) {
            $query->joinWith('transaction.lesson.subject');
            $query->joinWith('transaction.lesson.tutor.profile');
        }

        return $dataProvider;
    }

    public function getTotals()
    {
        $total = [];
        $total['countLessons'] = Lesson::findStudentLessons(\Yii::$app->user->id)->count();

        $transactionObjectTypes = [Transaction::TYPE_CLIENT_BALANCE_POST_PAYMENT, Transaction::TYPE_CLIENT_BALANCE_AUTO, Transaction::TYPE_CLIENT_BALANCE_MANUAL_CHARGE];
        $total['paid'] = Transaction::find()->select(['SUM(amount)'])
            ->andWhere(['objectType' => $transactionObjectTypes])
            ->andWhere(['objectId' => \Yii::$app->user->id])
            ->andWhere(['type' => Transaction::STRIPE_CHARGE])
            ->andWhere(['status' => Transaction::STATUS_SUCCESS])
            ->scalar();
        return $total;
    }

    public function getLinkWithParams()
    {
        return Url::to('/pdf/?dateFrom=' . \Yii::$app->formatter->asDateWithSlashes($this->dateFrom)
            . '&dateTo=' . \Yii::$app->formatter->asDateWithSlashes($this->dateTo));
    }
}

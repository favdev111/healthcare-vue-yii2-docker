<?php

namespace modules\payment\models\api\search;

use common\components\behaviors\FilterDatesBehavior;
use modules\account\models\api\AccountClient;
use modules\payment\models\api\CardInfo;
use modules\payment\models\api\Transaction;
use yii\data\ActiveDataProvider;

class TransactionSearch extends Transaction
{
    public $withoutFailed;

    private $onlySuccess = false;

    public function rules()
    {
        return array_merge(
            [
                [['studentId'], 'exist', 'skipOnError' => true, 'skipOnEmpty' => true, 'targetClass' => AccountClient::className(), 'targetAttribute' => ['studentId' => 'id']],
                [['withoutFailed'], 'integer']
            ],
            $this->getFilterDatesRulesArray()
        );
    }

    public function selectOnlySuccess($val)
    {
        $this->onlySuccess = $val;
        return $this;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [FilterDatesBehavior::className()]);
    }

    public function addFilterWithoutFailed($query)
    {
        if ($this->withoutFailed) {
            $query->andWhere([
                '<>',
                self::tableName() . '.status',
                static::STATUS_ERROR
            ]);
        }
        return $query;
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->load($params, '');

        $query = self::findWithoutConditions();
        $query->excludeRefundOfLessonTransfers();
        if ($this->onlySuccess) {
            $query->andWhere(['status' => Transaction::STATUS_SUCCESS]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ],
            ],
        ]);

        $ownClientsQuery = AccountClient::find()->select('id');
        $ownClientCondition = [self::tableName() . '.studentId' => $ownClientsQuery];

        //if studentId not defined search all transaction of company with group transactions
        if (empty($this->studentId)) {
            $query->andWhere([
                'or',
                //transaction of company clients
                $ownClientCondition,
                //group charges related to company
                [
                    'and',
                    ['objectType' => static::TYPE_COMPANY_GROUP_PAYMENT],
                    ['objectId' => \Yii::$app->user->id]
                ]
            ])->andWhere([
                'or',
                //do not show partial refunds exclude partial refunds of group transactions
                ['not', ['type' => Transaction::PARTIAL_REFUND]],
                [
                    'and',
                    ['type' => Transaction::PARTIAL_REFUND],
                    ['objectType' => Transaction::TYPE_LESSON]
                ]
            ]);
        } else {
            $query->andWhere($ownClientCondition);
            //do not add partial refunds to result if this is not full search that includes group transaction
            $query->andWhere([
                '<>',
                self::tableName() . '.type',
                static::PARTIAL_REFUND
            ]);
        }

        //do not show transfers from platform to tutor
        $query->andWhere(['not', ['objectType' => Transaction::TYPE_LESSON_BATCH_PAYMENT]]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');
            return $dataProvider;
        }
        $query = $this->filterDate($query, static::tableName());

        $this->addFilterWithoutFailed($query);
        $query->andFilterWhere([
            self::tableName() . '.studentId' => $this->studentId,
        ]);
        return $dataProvider;
    }
}

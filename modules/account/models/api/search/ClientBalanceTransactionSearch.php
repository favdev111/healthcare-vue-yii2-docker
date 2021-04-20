<?php

namespace modules\account\models\api\search;

use common\components\behaviors\FilterDatesBehavior;
use modules\account\models\AccountNote;
use modules\account\models\api\AccountClient;
use modules\account\models\api\ClientBalanceTransaction;
use yii\data\ActiveDataProvider;

class ClientBalanceTransactionSearch extends ClientBalanceTransaction
{
    private $disablePagination = false;
    public function rules()
    {
        return array_merge(
            [
                [['clientId'], 'exist', 'skipOnError' => true, 'targetClass' => AccountClient::className(), 'targetAttribute' => ['clientId' => 'id']],
            ],
            $this->getFilterDatesRulesArray()
        );
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [FilterDatesBehavior::className()]);
    }

    public function disablePagination($val)
    {
        $this->disablePagination = $val;
        return $this;
    }


    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        // This method is used to search for own jobs. Showing all including suspended
        $query = self::find();

        $providerParams = [
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ],
            ]
        ];
        if ($this->disablePagination) {
            $providerParams = array_merge($providerParams, ['pagination' => false]);
        }

        $dataProvider = new ActiveDataProvider($providerParams);

        $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
             $query->where('0=1');
            return $dataProvider;
        }

        $query = $this->filterDate($query, static::tableName());

        $query->andFilterWhere([self::tableName() . '.clientId' => $this->clientId]);

        $query->andWhere(['hide' => 0]);

        return $dataProvider;
    }
}

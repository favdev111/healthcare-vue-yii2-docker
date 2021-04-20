<?php

namespace modules\account\models\api\search;

use modules\account\models\api\EmployeeClient;
use yii\data\ActiveDataProvider;

class EmployeeClientsSearch extends EmployeeClient
{
    public function rules()
    {
        return [
          [['employeeId', 'clientId'], 'integer']
        ];
    }

    public function search($params)
    {
        $query = EmployeeClient::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ],
            ],
        ]);

        if (!$this->load($params, '') && !$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['clientId' => $this->clientId]);
        $query->andFilterWhere(['employeeId' => $this->clientId]);


        return $dataProvider;
    }
}

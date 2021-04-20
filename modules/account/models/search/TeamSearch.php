<?php

namespace modules\account\models\search;

use modules\account\models\Team;
use yii\data\ActiveDataProvider;

class TeamSearch extends Team
{
    public function rules()
    {
        return [
            ['name', 'string']
        ];
    }

    public function search(array $params = []): ActiveDataProvider
    {
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_ASC,
                ],
            ],
        ]);

        $this->load($params, '');
        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }
}

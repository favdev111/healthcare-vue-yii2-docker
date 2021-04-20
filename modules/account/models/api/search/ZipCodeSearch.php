<?php

namespace modules\account\models\api\search;

use modules\account\models\api\ZipCode;
use yii\data\ActiveDataProvider;

class ZipCodeSearch extends ZipCode
{
    public function search($params)
    {
        $query = ZipCode::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }
        $query->andFilterWhere([
            self::tableName() . '.code' => $this->code,
        ]);

        $query->distinct();

        return $dataProvider;
    }
}

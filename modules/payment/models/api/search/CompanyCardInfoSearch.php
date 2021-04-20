<?php

namespace modules\payment\models\api\search;

use modules\payment\models\api\CompanyCardInfo;
use yii\data\ActiveDataProvider;

class CompanyCardInfoSearch extends CompanyCardInfo
{
    public function rules()
    {
        return [
        ];
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        // This method is used to search for own jobs. Showing all including suspended
        $query = self::find()->joinWith('account');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'updatedAt' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
             $query->where('0=1');
            return $dataProvider;
        }
        return $dataProvider;
    }
}

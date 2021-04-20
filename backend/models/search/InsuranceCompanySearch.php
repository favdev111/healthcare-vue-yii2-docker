<?php

namespace backend\models\search;

use backend\models\InsuranceCompany;
use yii\data\ActiveDataProvider;

class InsuranceCompanySearch extends InsuranceCompany
{
    public static function tableName()
    {
        return 'insurance_company';
    }

    public function rules()
    {
        return [
            ['name', 'string']
        ];
    }

    public function search($params): ActiveDataProvider
    {
        $query = InsuranceCompany::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $query->orderBy(['name' => SORT_ASC]);
        $this->load($params, '');
        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }
        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}

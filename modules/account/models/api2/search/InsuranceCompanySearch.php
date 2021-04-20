<?php

namespace modules\account\models\api2\search;

use modules\account\models\api2\InsuranceCompany;
use yii\data\ActiveDataProvider;

class InsuranceCompanySearch extends InsuranceCompany
{
    public static function tableName()
    {
        return '{{%insurance_company}}';
    }

    public function rules()
    {
        return [
            [['name'], 'string']
        ];
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
        $query = InsuranceCompany::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeLimit' => [20, 300],
            ],
        ]);

        $query->orderBy(['name' => SORT_ASC]);
        $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}

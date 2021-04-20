<?php

namespace modules\account\models\api2\search;

use modules\account\models\api2\AutoimmuneDisease;
use modules\account\models\api2\HealthGoal;
use yii\data\ActiveDataProvider;

class AutoimmuneDiseaseSearch extends HealthGoal
{
    public static function tableName()
    {
        return '{{%autoimmune_disease}}';
    }

    public function rules()
    {
        return [
            [['name'], 'string'],
            [['description'], 'string']
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
        $query = AutoimmuneDisease::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeLimit' => [20, 300],
            ],
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'name', $this->name]);
        return $dataProvider;
    }
}

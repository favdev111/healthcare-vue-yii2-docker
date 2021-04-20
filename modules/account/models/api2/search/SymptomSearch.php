<?php

namespace modules\account\models\api2\search;

use modules\account\models\api2\HealthGoal;
use modules\account\models\api2\Symptom;
use yii\data\ActiveDataProvider;

class SymptomSearch extends HealthGoal
{
    public static function tableName()
    {
        return '{{%symptom}}';
    }

    public function rules()
    {
        return [
            [['name'], 'string'],
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
        $query = Symptom::find();

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

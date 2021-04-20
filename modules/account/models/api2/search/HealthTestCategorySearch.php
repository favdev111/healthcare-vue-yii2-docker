<?php

namespace modules\account\models\api2\search;

use modules\account\models\api2\HealthTestCategory;
use yii\data\ActiveDataProvider;

class HealthTestCategorySearch extends HealthTestCategory
{
    public static function tableName()
    {
        return '{{%health_test_category}}';
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
        $query = HealthTestCategory::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'name', $this->name]);
        return $dataProvider;
    }
}

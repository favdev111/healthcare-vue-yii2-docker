<?php

namespace modules\account\models\api2\search;

use modules\account\models\api2\State;
use modules\account\models\ar\StaticPage;
use yii\data\ActiveDataProvider;

class StaticPageSearch extends State
{
    public static function tableName()
    {
        return '{{%static_page}}';
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
        $query = StaticPage::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
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

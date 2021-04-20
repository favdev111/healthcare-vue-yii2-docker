<?php

namespace modules\account\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class SearchDataSearch extends SearchData
{
    public $whoIs;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['search', 'page', 'who', 'createdAt', 'updatedAt', 'whoIs', 'zipCode'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
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
        $query = SearchData::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        $dataProvider->setSort([
            'attributes' => [
                'createdAt',
                'whoIs' => [
                    'asc' => ['who' => SORT_ASC],
                    'desc' => ['who' => SORT_DESC],
                    'label' => 'Who',
                    'default' => SORT_ASC,
                ],
                'search',
                self::tableName() . '.zipCode'
            ],
            'defaultOrder' => ['createdAt' => SORT_DESC],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->joinWith(['account.profile' => function ($q) {
            $q->andFilterWhere(
                [
                    'or',
                    ['like', 'firstName', $this->whoIs],
                    ['like', 'lastName', $this->whoIs],
                ]
            );
        }
        ]);

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'page' => $this->page,
             self::tableName() . '.zipCode' => $this->zipCode,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ]);

        $query->andFilterWhere(['like', 'search', $this->search]);

        return $dataProvider;
    }
}

<?php

namespace modules\account\models\backend;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AccountSearch represents the model behind the search form about `modules\account\models\backend\Account`.
 */
class AccountSearch extends Account
{
    public $firstName;
    public $lastName;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['email', 'firstName', 'lastName', 'status'], 'safe'],
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
    }

    protected function dataProvider($query, $params)
    {
        $query->joinWith(['profile p'], true);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ],
            ],
        ]);

        /**
         * load method for form load data. setAttributes without form usage
         */
        $this->load($params) || $this->setAttributes($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'account.id' => $this->id,
            'account.status' => $this->status,
        ]);

        $query
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'p.firstName', $this->firstName])
            ->andFilterWhere(['like', 'p.lastName', $this->lastName]);

        return $dataProvider;
    }
}

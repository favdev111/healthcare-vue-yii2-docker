<?php

namespace common\models;

use modules\account\models\api\PostPayment;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PostPaymentSearch represents the model behind the search form about `common\models\PostPayment`.
 */
class PostPaymentSearch extends PostPayment
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'accountId'], 'integer'],
            [['amount'], 'number'],
            [['date'], 'safe'],
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
        $query = PostPayment::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'accountId' => $this->accountId,
            'amount' => $this->amount,
            'date' => $this->date,
        ]);

        return $dataProvider;
    }
}

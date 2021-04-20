<?php

namespace modules\chat\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use modules\chat\models\ChatMessage;

/**
 * ChatSearch represents the model behind the search form about `modules\chat\models\Chat`.
 */
class ChatSearch extends Chat
{
    public $onlySuspicious;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['onlySuspicious', 'boolean'],
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
        $query = Chat::find();
        $query->with('account.profile');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->onlySuspicious) {
            $query->andWhere(['not', ['status' => Chat::STATUS_ACTIVE]]);
        }

        // grid filtering conditions

        return $dataProvider;
    }
}

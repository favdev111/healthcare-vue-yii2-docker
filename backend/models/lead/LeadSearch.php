<?php

namespace backend\models\lead;

use DateTime;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Lead;
use yii\db\Expression;

/**
 * LeadSearch represents the model behind the search form of `common\models\Lead`.
 */
class LeadSearch extends Lead
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['createdAt', 'date'],
            [['id', 'status'], 'integer'],
            [['name', 'phoneNumber', 'email', 'data', 'advertisingChannel', 'source', 'createdAt', 'updatedAt', 'clickId', 'externalId', 'ip'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = Lead::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'updatedAt' => $this->updatedAt,
        ]);

        if ($this->createdAt) {
            $dateTime = DateTime::createFromFormat('m-d-Y', $this->createdAt)->format('Y-m-d');
            $expression = new Expression('date(createdAt) = :date', ['date' => $dateTime]);
            $query->andWhere($expression);
        }

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'phoneNumber', $this->phoneNumber])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'data', $this->data])
            ->andFilterWhere(['like', 'advertisingChannel', $this->advertisingChannel])
            ->andFilterWhere(['like', 'source', $this->source])
            ->andFilterWhere(['like', 'clickId', $this->clickId])
            ->andFilterWhere(['like', 'externalId', $this->externalId])
            ->andFilterWhere(['like', 'ip', $this->ip]);

        return $dataProvider;
    }
}

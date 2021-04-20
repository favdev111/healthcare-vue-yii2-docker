<?php

namespace modules\account\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use modules\account\models\ChangeLog;

/**
 * ChangeLogSearch represents the model behind the search form of `modules\account\models\ChangeLog`.
 */
class ChangeLogSearch extends ChangeLog
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'objectType', 'actionType', 'madeBy', 'objectId'], 'integer'],
            [['oldValue', 'newValue', 'date', 'description'], 'safe'],
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
        $query = ChangeLog::find();

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
            'objectType' => $this->objectType,
            'actionType' => $this->actionType,
            'madeBy' => $this->madeBy,
            'objectId' => $this->objectId,
            'date' => $this->date,
        ]);

        $query->andFilterWhere(['like', 'oldValue', $this->oldValue])
            ->andFilterWhere(['like', 'newValue', $this->newValue])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}

<?php

namespace backend\models\allergy;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\health\allergy\Allergy;

/**
 * AllergySearch represents the model behind the search form of `common\models\health\allergy\Allergy`.
 */
class AllergySearch extends Allergy
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'allergyCategoryId'], 'integer'],
            [['name', ], 'safe'],
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
        $query = Allergy::find();

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
            'allergyCategoryId' => $this->allergyCategoryId,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}

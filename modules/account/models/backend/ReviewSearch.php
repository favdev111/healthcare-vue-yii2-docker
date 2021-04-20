<?php

namespace modules\account\models\backend;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ReviewSearch represents the model behind the search form about `modules\account\models\Review`.
 */
class ReviewSearch extends Review
{
    public $accountEmail;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['accountId', 'lessonId', 'articulation', 'proficiency', 'punctual', 'hours', 'accounts', 'status'], 'integer'],
            [['createdAt', 'updatedAt'], 'safe'],
            [['message', 'name', 'accountEmail'], 'string']
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
        $query = self::find();
        $query->joinWith('account ac');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ],
            ],
        ]);

        $dataProvider->sort->attributes['accountEmail'] = [
            'asc' => ['ac.email' => SORT_ASC],
            'desc' => ['ac.email' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->andFilterWhere([
            self::tableName() . '.id' => $this->id,
            'accountId' => $this->accountId,
            'lessonId' => $this->lessonId,
            'articulation' => $this->articulation,
            'proficiency' => $this->proficiency,
            'punctual' => $this->punctual,
            'hours' => $this->hours,
            'accounts' => $this->accounts,
            self::tableName() . '.status' => $this->status,
            self::tableName() . '.createdAt' => $this->createdAt,
            self::tableName() . '.updatedAt' => $this->updatedAt,
        ]);

        $query->andFilterWhere(['like', 'message', $this->message]);
        $query->andFilterWhere(['like', 'ac.email', $this->accountEmail]);

        return $dataProvider;
    }
}

<?php

namespace modules\account\models\search;

use modules\account\models\AccountNote;
use yii\data\ActiveDataProvider;

class AccountNoteSearch extends AccountNote
{
    public function rules()
    {
        return [
            ['content', 'string'],
            ['accountId', 'integer'],
        ];
    }

    public function search($data = null)
    {
        $query = parent::find();
        $this->load($data);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['createdAt' => SORT_DESC],
            ],
        ]);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['accountId' => $this->accountId]);
        $query->andFilterWhere(['content' => $this->content]);

        return $dataProvider;
    }
}

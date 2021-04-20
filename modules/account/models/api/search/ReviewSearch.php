<?php

namespace modules\account\models\api\search;

use modules\account\models\api\Tutor;
use modules\account\models\api\tutor\Review;
use yii\data\ActiveDataProvider;

class ReviewSearch extends Review
{
    public function rules()
    {
        return [
            [['accountId'], 'exist', 'skipOnError' => true, 'targetClass' => Tutor::className(), 'targetAttribute' => ['accountId' => 'id']],
        ];
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        // This method is used to search for own jobs. Showing all including suspended
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([self::tableName() . '.accountId' => $this->accountId]);
        $query->andFilterWhere(['<>', self::tableName() . '.status', Review::BANNED]);

        $query->distinct();

        return $dataProvider;
    }
}

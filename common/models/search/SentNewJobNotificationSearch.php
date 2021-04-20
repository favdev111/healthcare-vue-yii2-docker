<?php

namespace common\models\search;

use common\models\City;
use common\models\SentNewJobNotification;
use modules\account\models\Category;
use modules\account\models\Profile;
use modules\account\models\Subject;
use yii\data\ActiveDataProvider;

class SentNewJobNotificationSearch extends SentNewJobNotification
{
    public $jobName;
    public $firstName;
    public $lastName;
    public function rules()
    {
        return [
            [['firstName', 'lastName', 'jobName'], 'string'],
            [['accountId', 'jobId', 'totalScore'], 'integer']
        ];
    }

    public function search($params)
    {
        $query = static::find()->joinWith('account.profile')->joinWith('job.subjects')->joinWith('job.categories')->joinWith('job.city')->distinct();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $dataProvider->sort->attributes['jobName'] = [
            'asc' => [City::tableName() . '.name' => SORT_ASC],
            'desc' => [City::tableName() . '.name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['createdAt'] = [
            'asc' => ['createdAt' => SORT_ASC, 'id' => SORT_DESC],
            'desc' => ['createdAt' => SORT_DESC, 'id' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['firstName'] = [
            'asc' => [Profile::tableName() . '.firstName' => SORT_ASC],
            'desc' => [Profile::tableName() . '.firstName' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['lastName'] = [
            'asc' => [Profile::tableName() . '.lastName' => SORT_ASC],
            'desc' => [Profile::tableName() . '.lastName' => SORT_DESC],
        ];

        $this->load($params, '');

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['totalScore' => $this->totalScore]);

        $query->andFilterWhere(['like', Profile::tableName() . '.firstName', $this->firstName]);
        $query->andFilterWhere(['like', Profile::tableName() . '.lastName', $this->lastName]);

        $query->andFilterWhere([static::tableName() . '.accountId' => $this->accountId]);
        $query->andFilterWhere([static::tableName() . '.jobId' => $this->jobId]);

        $query->andFilterWhere([
            'or',
            ['like', Subject::tableName() . '.name', $this->jobName],
            ['like', City::tableName() . '.name', $this->jobName],
            ['like', Category::tableName() . '.name', $this->jobName],
        ]);

        return $dataProvider;
    }
}

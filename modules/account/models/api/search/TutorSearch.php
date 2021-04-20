<?php

namespace modules\account\models\api\search;

use modules\account\models\AccountCompanyStatistic;
use modules\account\models\IgnoredTutorsJob;
use modules\account\models\Job;
use modules\account\models\Profile;
use modules\account\models\api\Tutor;
use yii\data\ActiveDataProvider;

class TutorSearch extends Tutor
{
    public $fullName;
    public $useElastic = false;
    public $excludeHiddenSearch = true;

    public function rules()
    {
        return [
            ['fullName', 'string'],
            ['useElastic', 'boolean'],
            ['excludeHiddenSearch', 'boolean'],
        ];
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        if (!empty($params['useElastic'])) {
            $searchModel = new \modules\account\models\TutorSearch();
            //data provider should contains api/Tutor models
            $searchModel->returnedClass = Tutor::class;
            //tutor should have all selected subjects
            $searchModel->subjectCompareCondition = \modules\account\models\TutorSearch::COMPARE_CONDITION_AND;
            $searchModel->load($params, '');
            return $searchModel->search($params['per-page'] ?? 20);
        } else {
            // This method is used to search for own jobs. Showing all including suspended
            $query = self::find()->joinWith('profile');
            self::addAppliedTutorCondition($query);
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'sort' => [
                    'defaultOrder' => [
                        'updatedAt' => SORT_DESC,
                    ],
                ],
            ]);

            $this->load($params, '');

            if (!$this->validate()) {
                // uncomment the following line if you do not want to return any records when validation fails
                $query->where('0=1');
                return $dataProvider;
            }

            if ($this->excludeHiddenSearch) {
                $query->andWhere(['searchHide' => false]);
            }
            $query->andWhere(['status' => self::STATUS_ACTIVE]);

            $query->andFilterWhere([
                'or',
                ['like', Profile::tableName() . '.firstName', $this->fullName],
                ['like', Profile::tableName() . '.lastName', $this->fullName],
            ]);

            $query->distinct();

            return $dataProvider;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTutorProfile()
    {
        return $this->hasOne(Profile::className(), ['accountId' => 'tutorId']);
    }
}

<?php

namespace modules\account\models\api\search;

use common\components\behaviors\FilterDatesBehavior;
use modules\account\models\api\Lesson;
use modules\account\models\Subject;
use yii\data\ActiveDataProvider;

class LessonSearch extends Lesson
{
    public $keyword;
    private $disablePagination = false;
    public $withRelatedModels = false;

    public function disablePagination()
    {
        $this->disablePagination = true;
        return $this;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [FilterDatesBehavior::className()]);
    }

    public function rules()
    {
        return array_merge($this->getLessonFilterDateRules(), [
          [['keyword'], 'string']
        ], [
            ['toDate', 'validateToDate', 'skipOnError' => true],
        ]);
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['index'] = ['fromDate', 'toDate', 'keyword'];
        return $scenarios;
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->setScenario('index');
        $this->load($params, '');
        if (!$this->validate()) {
            return $this;
        }

        $query = self::find();

        $providerParams = [
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ],
            ],
        ];
        if ($this->disablePagination) {
            $providerParams = array_merge($providerParams, ['pagination' => false]);
        }
        $dataProvider = new ActiveDataProvider($providerParams);

        if (!empty(($this->keyword)) || $this->withRelatedModels) {
            $query->joinWith('subject');
            $query->joinWith('student st');
            $query->joinWith('tutor tut');
        }
        if (!empty($this->keyword)) {
            $query->joinWith('studentProfile stProfile');
            $query->joinWith('tutorProfile tutProfile');
            $query->andWhere(['or',
                ['like', Subject::tableName() . '.name', $this->keyword],
                ['like', 'CONCAT(stProfile.firstName, " ", stProfile.lastName)', $this->keyword],
                ['like', 'CONCAT(tutProfile.firstName, " ", tutProfile.lastName)', $this->keyword]

                ]);
        }

        $this->addDateLessonFilter($query, 'fromDate', Lesson::tableName());
        $this->addDateLessonFilter($query, 'toDate', Lesson::tableName());

        return $dataProvider;
    }
}

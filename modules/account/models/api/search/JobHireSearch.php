<?php

namespace modules\account\models\api\search;

use common\components\behaviors\FilterDatesBehavior;
use modules\account\models\api\AccountClient;
use modules\account\models\api\Job;
use modules\account\models\api\JobHire;
use modules\account\models\api\Profile;
use modules\account\models\api\Tutor;
use modules\account\models\JobSubject;
use modules\account\models\Subject;
use yii\data\ActiveDataProvider;

class JobHireSearch extends JobHire
{
    public $query;
    public $studentId;
    public $jobZipCode;

    public function rules()
    {
        return array_merge(
            [
                [['studentId'], 'exist', 'skipOnError' => true, 'targetClass' => AccountClient::className(), 'targetAttribute' => ['studentId' => 'id']],
                [['tutorId'], 'exist', 'skipOnError' => true, 'targetClass' => Tutor::className(), 'targetAttribute' => ['tutorId' => 'id']],
                [['jobId'], 'exist', 'skipOnError' => true, 'targetClass' => Job::className(), 'targetAttribute' => ['jobId' => 'id']],
                ['status', 'each', 'rule' => ['integer']],
                [['query', 'jobZipCode'], 'string'],
            ],
            $this->getFilterDatesRulesArray()
        );
    }

    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                FilterDatesBehavior::class,
            ]
        );
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        // This method is used to search for own jobs. Showing all including suspended
        $query = self::find()->joinWith('jobSubjects')->joinWith('tutorProfile tp')->joinWith('student');
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
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->andFilterWhere([
            AccountClient::tableName() . '.id' => $this->studentId,
            self::tableName() . '.jobId' => $this->jobId,
            'tutorId' => $this->tutorId,
            self::tableName() . '.status' => $this->status,
        ]);

        $query->andFilterWhere([
            'or',
            ['like', 'tp.firstName', $this->query],
            ['like', 'tp.lastName', $this->query],
            ['like', Subject::tableName() . '.name', $this->query],
        ]);

        if (!empty($this->jobZipCode)) {
            $query->joinWith('job');
            $query->andWhere(['like', Job::tableName() . '.zipCode', $this->jobZipCode . '%', false]);
        }

        $query = $this->filterDate($query, static::tableName());

        $query->distinct();

        return $dataProvider;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTutorProfile()
    {
        return $this->hasOne(Profile::className(), ['accountId' => 'tutorId']);
    }
}

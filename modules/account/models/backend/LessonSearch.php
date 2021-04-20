<?php

namespace modules\account\models\backend;

use common\components\behaviors\FilterDatesBehavior;
use modules\account\helpers\Timezone;
use modules\account\models\AccountWithDeleted;
use modules\account\models\Subject;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * LessonSearch represents the model behind the search form about `modules\account\models\Lesson`.
 */
class LessonSearch extends Lesson
{
    public $subjectName;
    public $studentName;
    public $tutorName;


    public function behaviors()
    {
        return array_merge(parent::behaviors(), [FilterDatesBehavior::className()]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['studentId', 'tutorId', 'subjectId'], 'integer'],
            [['hourlyRate', 'amount', 'fee'], 'number'],
            [['fromDate', 'toDate', 'paymentDate', 'createdAt', 'updatedAt'], 'safe'],
            [['subjectName', 'studentName', 'tutorName'], 'string']
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
        $query->joinWith('subject');
        $query->joinWith('student st');
        $query->joinWith('tutor tut');
        $query->joinWith('studentProfile stProfile');
        $query->joinWith('tutorProfile tutProfile');
        $query->joinWith('job.jobHire');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ],
            ],
        ]);

        $dataProvider->sort->attributes['subjectName'] = [
            'asc' => [Subject::tableName() . '.name' => SORT_ASC],
            'desc' => [Subject::tableName() . '.name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['studentName'] = [
            'asc' => ['CONCAT(stProfile.firstName, stProfile.lastName)' => SORT_ASC],
            'desc' => ['CONCAT(stProfile.firstName, stProfile.lastName)' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['tutorName'] = [
            'asc' => ['CONCAT(tutProfile.firstName, tutProfile.lastName)' => SORT_ASC],
            'desc' => ['CONCAT(tutProfile.firstName, tutProfile.lastName)' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $this->addDateLessonFilter($query, 'fromDate', Lesson::tableName());
        $this->addDateLessonFilter($query, 'toDate', Lesson::tableName());

        $query->andFilterWhere([
            self::tableName() . '.id' => $this->id,
            'studentId' => $this->studentId,
            'tutorId' => $this->tutorId,
            'subjectId' => $this->subjectId,
            'hourlyRate' => $this->hourlyRate,
            'paymentDate' => $this->paymentDate,
            'amount' => $this->amount,
            'fee' => $this->fee,
            self::tableName() . '.updatedAt' => $this->updatedAt,
        ]);

        $query->andFilterWhere(['like', Subject::tableName() . '.name', $this->subjectName]);
        $query->andFilterWhere(['like', 'CONCAT(stProfile.firstName, " ", stProfile.lastName)', $this->studentName]);
        $query->andFilterWhere(['like', 'CONCAT(tutProfile.firstName, " ", tutProfile.lastName)', $this->tutorName]);

        return $dataProvider;
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(AccountWithDeleted::className(), ['id' => 'studentId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTutor()
    {
        return $this->hasOne(AccountWithDeleted::className(), ['id' => 'tutorId']);
    }
}

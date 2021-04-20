<?php

namespace modules\account\models\search;

use common\components\behaviors\FilterDatesBehavior;
use modules\account\models\Lesson;
use modules\payment\models\Transaction;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class LessonSearch extends Lesson
{
    const MOBILE_PAGE_SIZE = 4;
    const DESKTOP_PAGE_SIZE = 10;
    public $dateTo;
    public $dateFrom;
    protected $addOwnCondition = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['dateFrom', 'date', 'type' => 'datetime', 'format' => 'php:m/d/Y', 'timestampAttribute' => 'dateFrom', 'timestampAttributeFormat' => 'php:Y-m-d'],
            ['dateTo', 'date', 'type' => 'datetime', 'format' => 'php:m/d/Y', 'timestampAttribute' => 'dateTo', 'timestampAttributeFormat' => 'php:Y-m-d'],
        ];
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [FilterDatesBehavior::className()]);
    }

    /**
     * @param $params
     * @param $pageSize
     *
     * @return ActiveDataProvider
     */
    public function search($params, $pageSize = 20)
    {
        $this->load($params, '');

        $query = self::find();

        $providerParams = [
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'defaultPageSize' => $pageSize,
            ],
        ];

        $dataProvider = new ActiveDataProvider($providerParams);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->joinWith('student st');
        $query->joinWith('tutor tut');


        //search only lessons related to current user
        if ($this->addOwnCondition) {
            $identity = \Yii::$app->user->identity;
            if ($identity->isTutor()) {
                $this->tutorId = \Yii::$app->user->id;
            } elseif ($identity->isPatient()) {
                $this->studentId = \Yii::$app->user->id;
            }
        }

        $query->andFilterWhere([static::tableName() . '.studentId' => $this->studentId]);
        $query->andFilterWhere([static::tableName() . '.tutorId' => $this->tutorId]);

        $this->addDateLessonFilter($query, 'dateFrom', Lesson::tableName());
        $this->addDateLessonFilter($query, 'dateTo', Lesson::tableName());

        return $dataProvider;
    }

    /**
     * Search for mobile
     *
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchMobile($params)
    {
        return $this->search($params, self::MOBILE_PAGE_SIZE);
    }

    /**
     * Search for desktop
     *
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchDesktop($params)
    {
        return $this->search($params, self::DESKTOP_PAGE_SIZE);
    }

    public function addOwnCondition()
    {
        $this->addOwnCondition = true;
    }

    /**
     * @return $this
     */
    public function addDefaultDateRangeCondition()
    {
        if (!$this->dateFrom) {
            $this->fillDefaultFrom();
        }
        if (!$this->dateTo) {
            $this->fillDefaultTo();
        }
        return $this;
    }

    protected function fillDefaultFrom()
    {
        $this->dateFrom = date('Y-m-d', strtotime('-30 days'));
    }

    protected function fillDefaultTo()
    {
        $this->dateTo = date('Y-m-d');
    }

    public static function getStudentTotals()
    {
        $totalLessonCount = Lesson::findStudentLessons(Yii::$app->user->id)->count();
        $lessonsIds = Lesson::findStudentLessons(Yii::$app->user->id)->select(Lesson::tableName() . '.id')->asArray()->column();
        if (Yii::$app->user->isPatient()) {
            // TODO: Calculate client total paid properly in case it is needed. Whole block is hidden in view at the moment
            $totalPaid = null;
        } else {
            $totalPaid = Transaction::find()
                ->byStudent(Yii::$app->user->identity)
                ->byObjectType(Transaction::TYPE_LESSON)
                ->byStatus([Transaction::STATUS_SUCCESS])
                ->andWhere(['objectId' => $lessonsIds])
                ->byType([Transaction::STRIPE_CHARGE])
                ->sum(new Expression('amount + fee'));
        }
        return [$totalLessonCount, $totalPaid];
    }
}

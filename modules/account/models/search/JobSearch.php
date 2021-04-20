<?php

namespace modules\account\models\search;

use common\helpers\Location;
use common\helpers\Url;
use common\models\Zipcode;
use common\components\ZipCodeHelper;
use modules\account\models\backend\Account;
use modules\account\models\Category;
use modules\account\models\CategorySubject;
use modules\account\models\IgnoredTutorsJob;
use modules\account\models\JobApply;
use modules\account\models\Subject;
use modules\account\models\SubjectOrCategory\AccountSubjectOrCategory;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use modules\account\models\Job;
use yii\data\Sort;
use yii\db\Expression;

/**
 * JobSearch represents the model behind the search form about `modules\account\models\Job`.
 */
class JobSearch extends Job
{
    const MOBILE_PAGE_SIZE = 2;
    const PER_PAGE_PAST_PARAM = 'per-page-past';
    const PAGE_SIZE_PAST_PARAM = 'page-size-past';
    const DISTANCE_FOR_ONLINE_LESSONS = -1;

    const DEFAULT_MILES_VALUE = 20;

    public $subject;
    public $miles;
    public $distance;

    public $age;
    public $hourlyRate;
    public $email;

    public $findZipCode;

    private static $_sort;

    public static $sort = [
        [
            'label' => 'Date & Time Posted',
            'direction' => SORT_DESC,
            'attribute' => 'updatedAt',
        ],
        [
            'label' => 'Distance',
            'direction' => SORT_ASC,
            'attribute' => 'distance',
        ],
        [
            'label' => 'Date of beginning lesson',
            'direction' => SORT_ASC,
            'attribute' => 'startLesson',
        ]
    ];

    public function init()
    {
        $this->module = Yii::$app->getModuleAccount();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'accountId',
                    'studentGrade',
                    'lessonOccur',
                    'ageFrom',
                    'ageTo',
                    'hourlyRateFrom',
                    'hourlyRateTo',
                    'startLesson',
                    'availability',
                    'subject',
                    'age',
                    'hourlyRate',
                    'miles',
                    'zipCode',
                    'id',
                ],
                'integer',
            ],
            [['gender', 'description', 'createdAt', 'updatedAt', 'status'], 'safe'],
            ['email', 'string'],
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

    public function setDefaultDistance(): void
    {
        if (!Yii::$app->user->isGuest) {
            $this->miles = JobSearch::DEFAULT_MILES_VALUE;
        }
    }

    public function getPastJob()
    {
        // Get all jobs without restrictions by suspended or block field
        $query = Job::findWithoutRestrictions();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 6,
            ],
            'sort' => [
                'defaultOrder' => [
                    'updatedAt' => SORT_DESC,
                ],
            ],
        ]);

        $query->andWhere(['!=', 'block', 1]);

        $query->andFilterWhere([
            'accountId' => Yii::$app->user->getId(),
            'close' => 1,
        ]);

        return $dataProvider;
    }

    /**
     * Search current jobs for mobile devices
     *
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchMobile($params)
    {
        $dataProvider = $this->searchStudentJobs($params);
        $dataProvider->getPagination()->setPageSize(self::MOBILE_PAGE_SIZE);

        return $dataProvider;
    }

    /**
     * Search current jobs for mobile devices
     *
     * @param $params
     * @return ActiveDataProvider
     */
    public function getPastJobMobile()
    {
        $dataProvider = $this->getPastJob();

        $dataProvider->getPagination()->setPageSize(self::MOBILE_PAGE_SIZE);
        $dataProvider->getPagination()->pageParam = self::PER_PAGE_PAST_PARAM;
        $dataProvider->getPagination()->pageSizeParam = self::PAGE_SIZE_PAST_PARAM;

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchStudentJobs($params)
    {
        // This method is used to search for own jobs. Showing all including suspended
        $query = Job::findWithoutRestrictions();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 6,
            ],
            'sort' => [
                'defaultOrder' => [
                    'updatedAt' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->andWhere(['job.status' => true]);
        $query->andWhere(['!=', 'close', 1]);
        $query->andWhere(['!=', 'block', 1]);
        $query->andFilterWhere([
            'id' => $this->id,
            'accountId' => Yii::$app->user->getId(),
            'studentGrade' => $this->studentGrade,
            'lessonOccur' => $this->lessonOccur,
            'ageFrom' => $this->ageFrom,
            'ageTo' => $this->ageTo,
            'hourlyRateFrom' => $this->hourlyRateFrom,
            'hourlyRateTo' => $this->hourlyRateTo,
            'startLesson' => $this->startLesson,
            'availability' => $this->availability,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ]);

        $query->andFilterWhere(['like', 'gender', $this->gender])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function backendSearch($params, $autogenerate = false)
    {
        $query = Job::findWithoutRestrictions();
        $query->joinWith('account');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                ],
            ],
        ]);

        $dataProvider->sort->attributes['age'] = [
            'asc' => ['ageFrom' => SORT_ASC, 'ageTo' => SORT_ASC],
            'desc' => ['ageTo' => SORT_DESC, 'ageFrom' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['hourlyRate'] = [
            'asc' => ['hourlyRateFrom' => SORT_ASC, 'hourlyRateTo' => SORT_ASC],
            'desc' => ['hourlyRateTo' => SORT_DESC, 'hourlyRateFrom' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['email'] = [
            'asc' => [Account::tableName() . '.email' => SORT_ASC],
            'desc' => [Account::tableName() . '.email' => SORT_DESC],
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
            'studentGrade' => $this->studentGrade,
            'lessonOccur' => $this->lessonOccur,
            'ageFrom' => $this->ageFrom,
            'ageTo' => $this->ageTo,
            'zipCode' => $this->zipCode,
            'hourlyRateFrom' => $this->hourlyRateFrom,
            'hourlyRateTo' => $this->hourlyRateTo,
            'startLesson' => $this->startLesson,
            'availability' => $this->availability,
            'job.status' => $this->status,
            self::tableName() . '.createdAt' => $this->createdAt,
            self::tableName() . '.updatedAt' => $this->updatedAt,
        ]);

        $query->andFilterWhere(['autogenerate' => $autogenerate]);

        $query->andFilterWhere([
            'and',
            ['<=', 'ageFrom', $this->age],
            ['>=', 'ageTo', $this->age],
        ]);

        $query->andFilterWhere([
            'and',
            ['<=', 'hourlyRateFrom', $this->hourlyRate],
            ['>=', 'hourlyRateTo', $this->hourlyRate],
        ]);

        $query->andFilterWhere(['like', 'gender', $this->gender])
            ->andFilterWhere(['like', 'description', $this->description]);

        $query->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }

    public function tutorJobSearch($params, $count = 20, $excludeClosed = true)
    {
        $account = Yii::$app->user->identity;
        $query = static::find();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $count,
            ],
            'sort' => $this->sort(),
        ]);
        $dataProvider->sort->defaultOrder = ['updatedAt' => SORT_DESC];

        $this->load($params, '');
        $query->joinWith('account');
        $query->andWhere(['<>', Account::tableName() . '.status', Account::SUSPENDED_STATUSES]);

        $query->andFilterWhere([Job::tableName() . '.id' => $this->id]);

        $query->joinWith([
            'jobSubjects' => function ($query) use ($account) {
                if ($this->subject) {
                    $subjectIds = [$this->subject];
                    $categoriesIds = CategorySubject::find()
                        ->select(['categoryId'])
                        ->andWhere(['subjectId' => $this->subject])
                        ->asArray()
                        ->column();
                } elseif ($account) {
                    $subjectIds = AccountSubjectOrCategory::find()
                        ->select('subjectId')
                        ->andWhere(['isCategory' => 0, 'accountId' => $account->id])
                        ->asArray()
                        ->all();
                    $categoriesIds = $account->getIdsOfRelatedCategories();
                } else {
                    $subjectIds = Subject::find()->select(['id'])->asArray()->column();
                    $categoriesIds = Category::find()->select(['id'])->asArray()->column();
                };
                if (!empty($categoriesIds)) {
                    $query->andWhere([
                        'or',
                        ['subjectId' => $subjectIds, 'isCategory' => 0],
                        ['subjectId' => $categoriesIds, 'isCategory' => 1],
                    ]);
                } else {
                    $query->andWhere(['subjectId' => $subjectIds, 'isCategory' => 0]);
                }
            },
        ]);

        $userLat = 0;
        $userLng = 0;
        if (!Yii::$app->user->isGuest) {
            $userLat = $account->profile->latitude;
            $userLng = $account->profile->longitude;
        } else {
            $zipCode = ZipCodeHelper::getZipCodeByUserId();
            $result = Location::getZipcodeLocation($zipCode);
            if ($result) {
                $userLat = $result['latitude'];
                $userLng = $result['longitude'];
            }
        }
        if ($userLat && $userLng) {
            $distanceQuery = '( 3959 * acos( cos( radians(' . $userLat . ') ) * cos( radians( ' .
                Zipcode::tableName() . '.latitude ) ) * cos( radians(' .
                Zipcode::tableName() . '.longitude) - radians(' . $userLng . ')) + sin(radians(' .
                $userLat . ')) * sin( radians(' . Zipcode::tableName() . '.latitude))))';

            $query->joinWith('zipCodeItem')
                ->addSelect(Job::tableName() . '.*')
                ->addSelect(Zipcode::tableName() . '.latitude')
                ->addSelect(Zipcode::tableName() . '.longitude')
                ->addSelect(new Expression('IF (lessonOccur = ' .
                    Job::LESSON_OCCUR_ONLINE . ', ' . static::DISTANCE_FOR_ONLINE_LESSONS . ', ' .
                    $distanceQuery . ' ) as distance'));
            $dataProvider->sort->attributes['distance'] = [
                'asc' => [new Expression('-distance DESC')],
                'desc' => ['distance' => SORT_DESC],
            ];
            $this->findZipCode = true;

            if ($this->miles) {
                if ($this->miles == static::DISTANCE_FOR_ONLINE_LESSONS) {
                    $query->andHaving(['distance' => static::DISTANCE_FOR_ONLINE_LESSONS]);
                } else {
                    $query->andHaving(['<', 'distance', $this->miles]);
                }
            }
        } else {
            $this->findZipCode = false;
            $query->addSelect(Job::tableName() . '.*')
                ->addSelect(new Expression('null as distance'));
        }

        $query->distinct();
        $query->andWhere([
            '>=', Job::tableName() . '.updatedAt', date('Y-m-d', strtotime($this->module->activeJobTime))
        ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andWhere(['job.status' => Job::PUBLISH]);
        if ($excludeClosed) {
            $query
                ->andWhere(['!=', 'close', 1])
                ->andWhere(['!=', 'block', 1]);
        }
        if ($account) {
            /**
             * Fix Property of non object error
             * @todo: Why account doesn't have related rate (\modules\account\models\Rate) model?
             */
            if ($account->rate) {
                $query->andWhere([
                    'and',
                    ['<=', 'hourlyRateFrom', $account->rate->hourlyRate],
                    ['>=', 'hourlyRateTo', $account->rate->hourlyRate],
                ]);
            }

            $query
                ->andWhere(['in', self::tableName() . '.gender', [$account->profile->gender, 'B']])
                ->andWhere([
                    'and',
                    ['<=', 'ageFrom', $account->profile->age],
                    ['>=', 'ageTo', $account->profile->age],
                ]);
        };
        if (!Yii::$app->user->isGuest) {
            $ignoredTutorJob = IgnoredTutorsJob::find()
                ->select('originJobId')
                ->andWhere(['tutorId' => Yii::$app->user->getId()])
                ->column();

            if (!empty($ignoredTutorJob)) {
                $query->andWhere([
                    'or',
                    ['not in', 'originJobId', $ignoredTutorJob],
                    ['is', 'originJobId', null],
                ]);
            }
        }

        return $dataProvider;
    }

    public function tutorNearJobSearch($params)
    {
        $account = Yii::$app->user->identity;
        $query = Job::find();

        $userLat = $account->profile->latitude;
        $userLng = $account->profile->longitude;
        $distanceQuery = '( 3959 * acos( cos( radians(' . $userLat . ') ) * cos( radians( ' .
            Zipcode::tableName() . '.latitude ) ) * cos( radians(' .
            Zipcode::tableName() . '.longitude) - radians(' . $userLng . ')) + sin(radians(' .
            $userLat . ')) * sin( radians(' . Zipcode::tableName() . '.latitude))))';

        $query->joinWith('zipCodeItem')
            ->addSelect(Job::tableName() . '.*')
            ->addSelect(Zipcode::tableName() . '.latitude')
            ->addSelect(Zipcode::tableName() . '.longitude')
            ->addSelect(new Expression('IF (lessonOccur = ' . Job::LESSON_OCCUR_ONLINE . ', ' .
                static::DISTANCE_FOR_ONLINE_LESSONS . ', ' . $distanceQuery . ' ) as distance'));

        $query->andHaving(['<', 'distance', 15]);

        $query->joinWith('account');
        $query->andWhere(['<>', 'account.status', Account::SUSPENDED_STATUSES]);
        $query->joinWith('account.profile');

        $query->joinWith(['jobSubjects' => function ($query) use ($account) {
            $subjectIds = AccountSubjectOrCategory::find()->select('subjectId')->andWhere(['isCategory' => 0, 'accountId' => $account->id])->asArray()->all();
            $categoriesIds = $account->getIdsOfRelatedCategories();
            if (!empty($categoriesIds)) {
                $query->andWhere([
                    'or',
                    ['subjectId' => $subjectIds, 'isCategory' => 0],
                    ['subjectId' => $categoriesIds, 'isCategory' => 1]
                ]);
            } else {
                $query->andWhere(['subjectId' => $subjectIds, 'isCategory' => 0]);
            }
        }
            ,
        ]);

        $query->andWhere([
            '>=', Job::tableName() . '.updatedAt', date('Y-m-d', strtotime($this->module->activeJobTime))
        ]);
        $query->andWhere(['job.status' => Job::PUBLISH]);

        /**
         * Fix Property of non object error
         * @todo: Why account doesn't have related rate (\modules\account\models\Rate) model?
         */
        if ($account->rate) {
            $query->andWhere([
                'and',
                ['<=', 'hourlyRateFrom', $account->rate->hourlyRate],
                ['>=', 'hourlyRateTo', $account->rate->hourlyRate],
            ]);
        }

        $query
            ->andWhere(['!=', 'close', 1])
            ->andWhere(['!=', 'block', 1])
            ->andWhere(['in', 'job.gender', [$account->profile->gender, 'B']])
            ->andWhere([
                'and',
                ['<=', 'ageFrom', $account->profile->age],
                ['>=', 'ageTo', $account->profile->age],
            ]);
        $query->orderBy(['updatedAt' => SORT_DESC]);

        $ignoredTutorJob = IgnoredTutorsJob::find()
            ->select('originJobId')
            ->andWhere(['tutorId' => Yii::$app->user->getId()])
            ->column();

        if (!empty($ignoredTutorJob)) {
            $query->andWhere([
                'or',
                ['not in', 'originJobId', $ignoredTutorJob],
                ['is', 'originJobId', null],
            ]);
        }
        $jobs = $query->all();
        $nearJobs = [];
        foreach ($jobs as $job) {
            if (!JobApply::findOne(['jobId' => $job->id, 'accountId' => Yii::$app->user->identity->id])) {
                array_push($nearJobs, $job);
            }
        }


        return $nearJobs;
    }

    public static function sort()
    {
        if (null !== self::$_sort) {
            return self::$_sort;
        }

        $attributes = [];
        foreach (self::$sort as $attribute) {
            $name = $attribute['attribute'];
            $direction = ($attribute['direction'] === SORT_ASC) ? 'asc' : 'desc';
            $reverseDirection = ($attribute['direction'] === SORT_ASC) ? 'desc' : 'asc';

            if (!empty($attribute['sort'])) {
                $sort = $attribute['sort'];
            } else {
                $sort = [
                    $name => $attribute['direction'],
                ];
            }

            $attributes[$name][$direction] = $sort;
            $attributes[$name][$reverseDirection] = [];
        }

        $sort = new Sort();
        $sort->attributes = $attributes;

        self::$_sort = $sort;

        return self::$_sort;
    }

    public static function forDropDown($valueAsUrl = true)
    {
        $sort = self::sort();
        $attributeOrders = $sort->getAttributeOrders();

        $attributes = [];
        foreach (self::$sort as $attribute) {
            $name = $attribute['attribute'];
            $direction = $attribute['direction'];
            $label = $attribute['label'];
            $param = (($attribute['direction'] === SORT_ASC) ? '' : '-') . $name;
            $attributes[$label] = $valueAsUrl ? Url::current([$sort->sortParam => $param]) : $param;

            if (
                isset($attributeOrders[$name])
                && ($attributeOrders[$name] === $direction)
            ) {
                $attributes['default'] = $label;
            }
        }

        if (!isset($attributes['default'])) {
            $attributes['default'] = self::$sort[0]['label'];
        }

        return $attributes;
    }

    public static function getDistanceList(bool $setFirstZero = false)
    {
        return [
            ($setFirstZero ? '0' : '') => 'Any distance',
            static::DISTANCE_FOR_ONLINE_LESSONS => 'Online',
            '5' => '5 miles',
            '10' => '10 miles',
            '15' => '15 miles',
            '20' => '20 miles',
            '30' => '30 miles',
            '50' => '50 miles',
        ];
    }
}

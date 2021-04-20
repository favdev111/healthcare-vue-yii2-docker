<?php

namespace modules\account\models\api\search;

use common\helpers\TutorSearchHelper;
use common\models\ProcessedEvent;
use modules\account\models\api\AccountClient;
use modules\account\models\api\Job;
use modules\account\models\AutomatchHistory;
use modules\account\models\Subject;
use yii\behaviors\AttributeTypecastBehavior;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

class JobSearch extends Job
{
    public $query;
    public $sort;
    public $isAutomatched;
    public $countProcessedBatches;

    const EMPTY_SEARCH_PARAMS_ARRAY = [
        'bool' => [
            'must' => [],
            'filter' => [],
            'must_not' => [],
        ],
    ];

    public static function setSubjectParams(&$params, $subjectIds)
    {
        array_push($params['bool']['must'], [
            'terms' => [
                'subjects' => $subjectIds,
            ],
        ]);
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'countProcessedBatches' => AttributeTypecastBehavior::TYPE_INTEGER,
                ],
                'typecastAfterValidate' => false,
                'typecastAfterFind' => true,
            ],
        ]);
    }

    public static function setGenderParams(&$params, $gender)
    {
        array_push($params['bool']['must'], [
            'match' => [
                'gender' => $gender,
            ],
        ]);
    }

    public static function setAgeParams(&$params, $from, $to)
    {
        array_push($params['bool']['must'], [
            'range' => [
                'dateOfBirth' => [
                    'lte' => 'now-' . $from . 'y',
                    'gte' => 'now-' . $to . 'y',
                    'format' => 'yyyy-mm-dd',
                ],
            ],
        ]);
    }

    public static function setDistanceParams(&$params, $lat, $lng, $distance = '15mi')
    {
        $distanceFilter = [
            'geo_distance' => [
                'distance' => $distance,
                'location' => [
                    'lat' => $lat,
                    'lon' => $lng,
                ],
            ],
        ];
        array_push($params['bool']['filter'], $distanceFilter);
    }

    public static function setHourlyRateParams(&$params, $gte, $lte, $isCompanyClient = false, $commission = null)
    {
        if ($isCompanyClient && !empty($commission)) {
            $filterScript = [
                'script' => TutorSearchHelper::generateHourlyRateScript($commission, $gte, $lte),
            ];
            array_push($params['bool']['filter'], $filterScript);
        } else {
            if (!is_null($commission) && $commission == 0) {
                $field = 'clearHourlyRate';
            } else {
                $field = 'hourlyRate';
            }
            array_push($params['bool']['must'], [
                'range' => [
                    $field => [
                        'gte' => $gte,
                        'lte' => $lte,
                    ],
                ],
            ]);
        }
    }

    public function rules()
    {
        return [
            ['query', 'string', 'max' => 255],
            ['close', 'boolean'],
            ['isAutomatched', 'boolean'],
            ['sort', 'string'],
            'accountExist' => [
                ['accountId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => AccountClient::class,
                'targetAttribute' => ['accountId' => 'id'],
            ],
        ];
    }

    /**
     * This method is used to search for own jobs. Showing all including suspended
     *
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $subQuery = (new Query())
            ->select(['COUNT(' . ProcessedEvent::tableName() . '.jobId)'])
            ->from(ProcessedEvent::tableName())
            ->andWhere([
                ProcessedEvent::tableName() . '.jobId' => new Expression('job.id'),
                ProcessedEvent::tableName() . '.type' => ProcessedEvent::TYPE_NEW_JOB_POSTED_NOTIFICATIONS_PROCESSED,
            ]);
        $query = self::find();
        $query->addSelect([static::tableName() . '.*', 'countProcessedBatches' => $subQuery]);
        $query->groupBy([static::tableName() . '.id']);
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
            return $dataProvider;
        }

        if (!empty($this->query)) {
            $query->joinWith('subjects')->joinWith('account.profile as studentProfile');
        }

        $query->andWhere([self::tableName() . '.status' => self::PUBLISH]);
        $query->andFilterWhere([
            self::tableName() . '.accountId' => $this->accountId,
            'studentGrade' => $this->studentGrade,
            'lessonOccur' => $this->lessonOccur,
            'hourlyRateFrom' => $this->hourlyRateFrom,
            'ageFrom' => $this->ageFrom,
            'ageTo' => $this->ageTo,
            'hourlyRateTo' => $this->hourlyRateTo,
            'startLesson' => $this->startLesson,
            'availability' => $this->availability,
        ]);

        $query->andFilterWhere([
            'or',
            ['like', self::tableName() . '.description', $this->query],
            ['like', Subject::tableName() . '.name', $this->query],
            ['like', 'studentProfile.firstName', $this->query],
            ['like', 'studentProfile.lastName', $this->query],
        ]);
        $query->andFilterWhere(['like', 'gender', $this->gender])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['close' => $this->close]);

        if (isset($this->isAutomatched)) {
            $query->joinWith('automatchHistory');
            if ($this->isAutomatched) {
                $query->andWhere(
                    [
                        'or',
                        ['isAutomatchEnabled' => 1],
                        ['not', [AutomatchHistory::tableName() . '.id' => null]]
                    ]
                );
            } else {
                $query->andWhere(['isAutomatchEnabled' => 0]);
                $query->andWhere([AutomatchHistory::tableName() . '.id' => null]);
            }
        }

        $query->distinct();

        return $dataProvider;
    }
}

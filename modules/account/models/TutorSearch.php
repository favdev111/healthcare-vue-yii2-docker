<?php

namespace modules\account\models;

use common\components\ElasticActiveDataProvider;
use common\components\Formatter;
use common\components\Pagination;
use common\helpers\Location;
use common\helpers\TutorSearchHelper;
use common\components\ZipCodeHelper;
use modules\account\components\TutorDataObject;
use modules\account\models\SubjectOrCategory\SubjectOrCategory;
use yii\base\Exception;
use yii\data\DataProviderInterface;
use yii\elasticsearch\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\data\Sort;
use yii\elasticsearch\ActiveRecord;
use yii\elasticsearch\Query;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class TutorSearch
 * @package modules\account\models
 *
 * @property array $subjects
 * @property boolean $receiveNewJobPostedNotifications
 * @property string $gender
 * @property integer $zipCode
 * @property integer $jobId
 * @property integer $countReviews
 * @property integer $status
 * @property integer $blockReason
 * @property float $hoursPerRelation
 */
class TutorSearch extends ActiveRecord
{
    const ADD_TO_RESULT = 200;

    const PAGE_SIZE_MOBILE = 8;
    const PAGE_SIZE_DESKTOP = 10;

    const SESSION_KEY = 'tutor-search';
    const SESSION_KEY_NO_TUTORS = 'no-tutors';
    const REDIRECT_FROM_LANDING = 'redirectedFromLanding';

    const ROTATED_TUTORS = 3;

    const DISTANCE_50_MILES = "50 miles";

    const ALWAYS_DISPLAY_SMALL_RESULT_POP_UP = true;

    /*at what quantities of tutor need to show "small result" pop-up */
    const SMALL_RESULT_POP_UP_LIMIT = 20;

    const EMPTY_SEARCH_PARAMS_ARRAY = array(
        'query' => [
            'bool' => [
                'must' => [],
                'filter' => [],
                'must_not' => [],
            ],
        ],
    );

    const COMPARE_CONDITION_OR = 'OR';
    const COMPARE_CONDITION_AND = 'AND';

    protected $sessionKey = null;

    protected $totalTutors = null;

    public $_id;

    public $fromAge;
    public $toAge;
    public $fromRate;
    public $toRate;
    public $commission;
    public $distance = '15mi';
    public $selectNewTutors = false;
    public $excludedTutorsIds = [];
    public $minimalRating;
    public $lastSearchParams = [];
    public $returnedClass = null;
    public $page;

    public $subjectCompareCondition = null;
    //use condition specified to LP
    public $addLpSearchCondition = false;

    public $excludeRotateSort = ['-hourlyRate', 'hourlyRate', '-rating'];

    protected $zipCodeLocation;

    private $isRotateSortExcluded = null;
    private $isResultCheck = true;
    private $_useCurrentUserLocation = true;
    private $_sort;

    private $addingTutors = false;
    private $jobToCompare = false;
    private $distanceSort;

    public $flashes;

    //profiles hidden from landing pages
    private $excludeHiddenProfiles = false;
    //account hidden from tutor search
    public $excludeHiddenSearch = true;

    private $lastVisitParamsArray;
    private $distanceScoreArray;

    public $sort = [
        'hourlyRate_asc' => [
            'label' => 'Lowest price',
            'direction' => SORT_ASC,
            'attribute' => 'hourlyRate',
        ],
        'hourlyRate_desc' => [
            'label' => 'Highest price',
            'direction' => SORT_DESC,
            'attribute' => 'hourlyRate',
        ],
        'rating_desc' => [
            'label' => 'Rating',
            'direction' => SORT_DESC,
            'attribute' => 'rating',
        ],
    ];

    public static function index()
    {
        return 'account';
    }

    public static function type()
    {
        return '_doc';
    }

    public function setLpSearchCondition($value)
    {
        $this->addLpSearchCondition = $value;
        return $this;
    }

    /**
     * @param $providerTotalCount
     * @return int
     */
    public static function getSearchResultCount($providerTotalCount)
    {
        return ($providerTotalCount > 100) ? $providerTotalCount : $providerTotalCount + self::ADD_TO_RESULT;
    }

    public function compareWithJob(Job $job)
    {
        $this->jobToCompare = $job;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function useCurrentUserLocation(bool $value)
    {
        $this->_useCurrentUserLocation = $value;
        return $this;
    }

    /**
     * @param bool $isRotateSortExcluded
     * @return $this
     */
    public function addIsRotateSortExcluded(bool $isRotateSortExcluded)
    {
        $this->isRotateSortExcluded = $isRotateSortExcluded;
        return $this;
    }

    /**
     * disable check results. For landings
     * @return $this
     */
    public function addNoResultsCheck()
    {
        $this->isResultCheck = false;
        return $this;
    }

    /**
     * @return boolean
     * @throws Exception
     */
    public function isRotateSortExcluded()
    {
        if ($this->isRotateSortExcluded === null) {
            throw new Exception("'isRotateSortExcluded' should be set");
        }

        return $this->isRotateSortExcluded;
    }

    /**
     * @param $pageSize integer
     * @return int
     */
    public function getCountOnPage($pageSize)
    {
        return $pageSize + self::ROTATED_TUTORS;
    }

    public function attributes()
    {
        return [
            'accountId',
            'rating',
            'gender',
            'subjects',
            'zipCode',
            'hourlyRate',
            'clearHourlyRate',
            'hours',
            'responseTime',
            'dateOfBirth',
            'cityId',
            'stateName',
            'location',
            'contentScore',
            'hoursScore',
            'ratingScore',
            'responseTimeScore',
            'totalScore',
            'distanceCalc',
            'lastVisit',
            'hideProfile',
            'availability',
            'createdAt',
            'receiveNewJobPostedNotifications',
            'countReviews',
            'fullName',
            'address',
            'searchHide',
            'status',
            'blockReason',
            'hoursPerRelation',
        ];
    }

    public function arrayAttributes()
    {
        return $this->attributes();
    }

    public function rules()
    {
        return [
            [[
                'rating',
                'gender',
                'subjects',
                'hourlyRate',
                'hours',
                'responseTime',
                'dateOfBirth',
                'contentScore',
                'hoursScore',
                'ratingScore',
                'responseTimeScore',
                'totalScore',
                'excludeHiddenSearch'
            ], 'safe'
            ],
            ['zipCode', 'string', 'max' => 5],
            [['status'], 'default', 'value' => Account::STATUS_ACTIVE],
            [['fromAge', 'toAge', 'fromRate', 'toRate', 'distance'], 'safe'],
            [['fullName', 'address'], 'string'],
            [['page', 'status', 'blockReason'], 'integer'],
            ['subjects', function () {
                if (is_string($this->subjects)) {
                    $this->subjects = explode(',', $this->subjects);
                }
            }
            ],
        ];
    }

    public static function getDistanceArray()
    {
        return [
            '15mi' => '15 miles',
            '20mi' => '20 miles',
            '30mi' => '30 miles',
            '50mi' => '50 miles',
        ];
    }

    public function getDistanceValue()
    {
        $array = $this->distanceArray;
        $distance = $this->distance;
        if (!empty($array[$distance])) {
            return $distance;
        }

        reset($array);
        return key($array);
    }

    /**
     * @return int
     */
    protected function getExcludePageSize($pageSize)
    {
        return $pageSize - self::ROTATED_TUTORS;
    }

    protected function getDistanceSortTutors($params, $limit = null)
    {
        $query = self::find()->query($params['query'] ?? $params);

        if ($this->zipcodeLocation['latitude'] && $this->zipcodeLocation['longitude']) {
            $query->scriptFields['distanceCalc'] = [
                'script' => "doc['location'].planeDistanceWithDefault({$this->zipcodeLocation['latitude']}, {$this->zipcodeLocation['longitude']}, 0) * 0.00062137",
            ];
        }

        if ($limit === null) {
            $limit = $query->count();
            $this->totalTutors = $limit;
        }

        $dataProviderParams = [
            'query' => $query,
            'sort' => $this->sort([], ['distance' => SORT_ASC]),
        ];
        if ($limit !== null) {
            $query->limit($limit);
            $dataProviderParams['Pagination'] = false;
        }
        $provider = new ActiveDataProvider($dataProviderParams);
        return $provider->getModels();
    }

    protected function getDistanceRangeScore($tutors, $pageSize = 6)
    {
        $countTutors = count($tutors);
        $scoreDistance = [];
        if (!$countTutors) {
            return $scoreDistance;
        }
        $newPageSize = $countTutors > $this->getCountOnPage($pageSize) ? $pageSize : $this->getCountOnPage($pageSize);
        if ($countTutors > $newPageSize) {
            $indexTutor = floor($countTutors * 0.2); // 20% of tutors
            $distanceScore = [30, 20, 10, -10, -15];
            $distanceScoreLength = count($distanceScore);
            for ($i = 1; $i <= $distanceScoreLength; $i++) {
                if ($i == 1) {
                    $scoreDistance[$distanceScore[$i - 1]] = $tutors[$indexTutor * ($i - 1)]->distanceCalc . '-' . $tutors[$indexTutor * $i]->distanceCalc;
                } elseif ($i == 5) {
                    $scoreDistance[$distanceScore[$i - 1]] = $tutors[$indexTutor * ($i - 1) + 1]->distanceCalc . '-' . $tutors[$indexTutor * $i - 1]->distanceCalc;
                } else {
                    $scoreDistance[$distanceScore[$i - 1]] = $tutors[$indexTutor * ($i - 1) + 1]->distanceCalc . '-' . $tutors[$indexTutor * $i]->distanceCalc;
                }
            }
        } else {
            $scoreDistance[30] = $tutors[0]->distanceCalc . '-' . $tutors[$countTutors - 1]->distanceCalc;
        }
        return $scoreDistance;
    }

    /**
     * @param $addParams
     * @return $this
     */
    public function excludeRotateSortParams($addParams)
    {
        if ($this->isRotateSortExcluded !== null) {
            return $this;
        }

        $this->addIsRotateSortExcluded(in_array(empty($addParams['sort']) ? '' : $addParams['sort'], $this->excludeRotateSort));

        return $this;
    }

    public function sortTop()
    {
        $this->sort['totalScore_asc'] = [
            'label' => 'Best results',
            'direction' => SORT_DESC,
            'attribute' => 'totalScore',
        ];
        return $this;
    }

    /*
     * @param      $dataProvider
     * @param      $pageSize
     * @param null $subjects
     */
    public static function addTopTutors(&$dataProvider, $pageSize, $subjects = null)
    {
        $extraData = self::searchTop($pageSize, $subjects)->getModels();
        $dataProvider->setModels(array_merge($dataProvider->getModels(), $extraData));
        $dataProvider->setKeys(array_keys($dataProvider->getModels()));
    }

    /**
     * Search tutors sorted by Total Score
     * @param null $pageSize
     * @param null $subjects
     * @return ActiveDataProvider
     */
    public static function searchTop($pageSize = null, $subjects = null)
    {
        $ts = new TutorSearch();
        if ($subjects) {
            $ts->subjects = $subjects;
        }
        $ts->distance = '50mi';
        $ts->useCurrentUserLocation(false);
        return $ts->sortTop()->search($pageSize);
    }

    /**
     * Remove no tutors session
     */
    public function removeNoTutorsFoundSession()
    {
        if (!empty(Yii::$app->session)) {
            Yii::$app->session->remove(static::SESSION_KEY_NO_TUTORS);
        }
    }

    /*REFACTORING*/
    public function resetSearchParamsExcept($exceptParam)
    {
        $param = $this->$exceptParam;
        $this->resetFields();
        $this->$exceptParam = $param;
    }

    public function addingTutors($val)
    {
        $this->addingTutors = $val;
        return $this;
    }

    public function fillStatus(array $params_array, $status = null)
    {
        //create array if it isn't
        if (!is_array($status)) {
            $status = [$status];
        }

        array_push($params_array['query']['bool']['must'], [
            'terms' => [
                'status' => $status,
            ],
        ]);
        return $params_array;
    }

    private function fillSubject($params_array, $subject, string $condition = 'OR')
    {
        //create array if it isn't
        if (!is_array($subject)) {
            $subject = [$subject];
        }

        //tutor should have at least one of the subjects
        if ($condition === static::COMPARE_CONDITION_OR) {
            //incoming data could contain categories ids, need to convert all categories id and use subject ids related to this categories
            $subject = SubjectOrCategory::convertToSubjectIds($subject);
            array_push($params_array['query']['bool']['must'], [
                'terms' => [
                    'subjects' => $subject,
                ],
            ]);
        } elseif ($condition === static::COMPARE_CONDITION_AND) {
            //tutor should have all selected subjects
            foreach ($subject as $subjectOrCategoryId) {
                //check is it category or subject
                if (SubjectOrCategory::isIdOfCategory($subjectOrCategoryId)) {
                    //if case of category look for all subject from category
                    $values = SubjectOrCategory::convertToSubjectIds([$subjectOrCategoryId]);
                } else {
                    $values = [$subjectOrCategoryId];
                }
                array_push($params_array['query']['bool']['must'], [
                    'terms' => [
                        'subjects' => $values,
                    ],
                ]);
            }
        }

        return $params_array;
    }

    public function fillBlockReason($params, $value)
    {
        array_push($params['query']['bool']['must'], [
            'match' => [
                'blockReason' => $value,
            ],
        ]);
    }

    private function fillSendNewJobPostedNotifications($paramsArray, $value): array
    {
        array_push($paramsArray['query']['bool']['filter'], [
            'term' => [
                'receiveNewJobPostedNotifications' => (int)$value,
            ],
        ]);
        return $paramsArray;
    }

    private function fillHideProfile($params_array, bool $value = false)
    {
        array_push($params_array['query']['bool']['filter'], [
            'term' => [
                'hideProfile' => (int)$value,
            ],
        ]);
        return $params_array;
    }

    private function fillMinimalRating(array $params, float $value, $includeZeroRating = false)
    {
        $should = [
            [
                'range' => ['rating' => ['gt' => $value]],
            ]
        ];

        if ($includeZeroRating) {
            array_push($should, [
                'bool' => [
                    'must' => [
                        [
                            'term' => [
                                'rating' => 0,
                            ],
                        ],
                        [
                            'term' => [
                                'countReviews' => 0,
                            ],
                        ],
                    ],
                ],
            ]);
        }

        array_push($params['query']['bool']['filter'], [
            'bool' => [
                'should' => $should,
            ],
        ]);

        return $params;
    }

    private function fillGender($params, $gender)
    {
        if (!empty($gender)) {
            array_push($params['query']['bool']['must'], [
                'match' => [
                    'gender' => $gender,
                ],
            ]);
        }
        return $params;
    }

    private function fillCity($params, $cityId)
    {
        array_push($params['query']['bool']['must'], [
            'match' => [
                'cityId' => $cityId,
            ],
        ]);
        return $params;
    }

    private function fillFullName(array $params, string $fullName): array
    {
        return $this->searchString($params, 'fullName', $fullName);
    }

    private function fillAddress(array $params, string $addressString): array
    {
        return $this->searchString($params, 'address', $addressString);
    }

    private function searchString($params, $field, $value)
    {
        array_push($params['query']['bool']['must'], [
            'match_phrase_prefix' => [$field => $value],
        ]);
        return $params;
    }

    private function fillState($params, $stateName)
    {
        array_push($params['query']['bool']['must'], [
            'match' => [
                'stateName' => $stateName,
            ],
        ]);
        return $params;
    }

    private function fillRate($params, $from, $to, $commission = null)
    {
        if (!empty(Yii::$app->user->id) && empty($commission)) {
            $identity = Yii::$app->user->identity;
            $commission = 0;
        }

        if (!empty($commission)) {
            $filterScript = [
                'script' => TutorSearchHelper::generateHourlyRateScript($commission, $from, $to),
            ];
            if (empty($params['query']['bool']['filter'])) {
                $params['query']['bool']['filter'] = [];
            }
            array_push($params['query']['bool']['filter'], $filterScript);
        } else {
            if (!is_null($commission) && $commission == 0) {
                $field = 'clearHourlyRate';
            } else {
                $field = 'hourlyRate';
            }
            array_push($params['query']['bool']['must'], [
                'range' => [
                    $field => [
                        'gte' => $from,
                        'lte' => $to,
                    ],
                ],
            ]);
        }
        return $params;
    }

    private function fillDateCreate($params, $period)
    {
        /**
         * @var Formatter $formatter
         */
        $toDate = time();
        $fromDate = strtotime($period, $toDate);
        array_push($params['query']['bool']['must'], [
            'range' => [
                'createdAt' => [
                    'gte' => $fromDate,
                    'lte' => $toDate,
                ],
            ],
        ]);
        return $params;
    }

    private function fillAgeParams($params, $from, $to)
    {
        array_push($params['query']['bool']['must'], [
            'range' => [
                'dateOfBirth' => [
                    'lt' => $from,
                    'gte' => $to,
                ],
            ],
        ]);
        return $params;
    }

    private function fillDistance($params, $distance, $lat, $lon)
    {
        $distanceFilter = [
            'geo_distance' => [
                'distance' => $distance,
                'location' => [
                    'lat' => $lat,
                    'lon' => $lon,
                ],
            ],
        ];
        array_push($params['query']['bool']['filter'], $distanceFilter);
        return $params;
    }

    private function setDistanceSort($lat, $lng)
    {
        $this->distanceSort = [
            'location' => [
                'lat' => $lat,
                'lon' => $lng,
            ],
            'order' => 'asc',
            'unit' => 'mi',
            'distance_type' => 'plane',
        ];
        $this->sort[] = [
            'label' => 'Distance',
            'direction' => SORT_ASC,
            'attribute' => 'distance',
            'sort' => [
                '_geo_distance' => $this->distanceSort,
            ],
        ];
    }


    private function setParamsFunctions($params, $functionArray)
    {
        if (empty($params['functions'])) {
            $params['functions'] = [];
        }
        $params['functions'][] = $functionArray;
        return $params;
    }

    private function setSortScoreAsc()
    {
        $bestResultSort = [
            '_score' => SORT_DESC,
        ];
        $this->sort['_score_asc'] = [
            'label' => 'Best results',
            'direction' => SORT_DESC,
            'attribute' => '_score',
            'sort' => $bestResultSort,
        ];
    }

    private function getSortScriptAddTutor($ids)
    {
        return array(
            'script_score' => [
                'script' => [
                    'source' => "
                         def id_a = doc['accountId']?.value;
                         for (id in params.ids) {
                            if (id_a == id) {
                               return doc['totalScore'].value + 10000;
                            }
                         }
                         return doc['totalScore'].value;
                    ",
                    'params' => [
                        'ids' => $ids,
                    ],
                ],
            ],
        );
    }

    private function setSortTotalScoreAsc()
    {
        $this->sort['totalScore_asc'] = [
            'label' => 'Best results',
            'direction' => SORT_DESC,
            'attribute' => 'totalScore',
        ];
    }

    private function excludeTutors($firstPage, $provider, $params, $pageSize, $newPageSize, $addParams)
    {
        if (!isset($params['function_score'])) {
            $params['function_score'] = [
                'query' => [
                    'bool' => [
                        'must' => [],
                        'must_not' => [],
                    ],
                ],
            ];
        }

        if ($firstPage) {
            $this->removeExcludeTutorsFromSession();
            $tutors = $provider->getModels();
            $excludeTutors = [];
            foreach ($tutors as $tutor) {
                array_push($excludeTutors, $tutor->getPrimaryKey());
            }
            array_push($params['function_score']['query']['bool']['must'], [
                'range' => [
                    'hoursScore' => [
                        'lte' => 5,
                    ],
                ],
            ]);
            array_push($params['function_score']['query']['bool']['must'], [
                'range' => [
                    'ratingScore' => [
                        'gte' => 5,
                        'lte' => 10,
                    ],
                ],
            ]);
            array_push($params['function_score']['query']['bool']['must_not'], [
                'terms' => [
                    '_id' => (array)$excludeTutors,
                ],
            ]);
            $rotateTutors = $this->getDistanceSortTutors($params, self::ROTATED_TUTORS);
            $includePosition = $pageSize >= 10 ? [2, 5, 9] : [2, 5, 7]; //tutors insert position
            $excludeRotateTutors = [];
            foreach ($rotateTutors as $key => $rotateTutor) {
                array_splice($tutors, $includePosition[$key], 0, array($rotateTutor));
                array_push($excludeRotateTutors, $rotateTutor->getPrimaryKey());
            }
            $provider->setModels($tutors);
            $provider->setKeys(array_keys($tutors));
            $this->setExcludeTutorsFromSession($excludeRotateTutors);
        } else {
            $excludeTutors = $this->getExcludeTutorsFromSession();
            array_push($params['function_score']['query']['bool']['must_not'], [
                'terms' => [
                    '_id' => (array)$excludeTutors,
                ],
            ]);
            $query = self::find()->query($params);
            $this->setDistanceCalc($query);
            $provider->query = $query;
            $pagination = new Pagination();
            $pagination->pageSize = $newPageSize;
            $pagination->params = $addParams;
            $provider->setPagination($pagination);
        }
        return $provider;
    }

    protected function excludeTutorsById(array $params, array $tutorsIds): array
    {
        if (!isset($params['function_score']['query']['bool']['must_not'])) {
            $params['function_score']['query']['bool']['must_not'] = [];
        }
        array_push($params['function_score']['query']['bool']['must_not'], [
            'terms' => [
                '_id' => $tutorsIds,
            ],
        ]);
        return $params;
    }

    /* END REFACTORING*/

    public function excludeHiddenProfiles(bool $value = true)
    {
        $this->excludeHiddenProfiles = $value;
        return $this;
    }

    public function excludeHiddenFromSearch($params): array
    {
        array_push($params['function_score']['query']['bool']['must_not'], [
            'term' => [
                'searchHide' => 1,
            ],
        ]);

        return $params;
    }

    public function fillLpConditions($params)
    {
        array_push($params['query']['bool']['must'], [
            'bool' => [
                'should' => [
                    [
                        'term' => ['status' => Account::STATUS_ACTIVE],
                    ],
                    [
                        'term' => ['blockReason' => Account::BLOCK_REASON_INACTIVE],
                    ]
                ],
            ],
        ]);

        return $params;
    }


    /**
     * @param int $pageSize
     * @param array $addparams
     * @return DataProviderInterface
     * @throws Exception
     */
    public function search($pageSize = 6, $addparams = [])
    {
        $this->removeNoTutorsFoundSession(); /*set self::SESSION_KEY_NO_TUTORS to null*/
        $this->zipCodeLocation = null;
        $params = self::EMPTY_SEARCH_PARAMS_ARRAY;

        $this->excludeRotateSortParams($addparams);

        if (!$this->validate()) {
            $dataProvider = new ArrayDataProvider();
            if ($this->hasErrors('zipCode')) {
                Yii::$app->session->setFlash('error', 'Invalid data. Please enter five-digit ZIP Code');
            }
            return $dataProvider;
        }

        $firstPage = false;

        //Use Lp search condition (includes active status(1) and blockedReason = 1)
        if ($this->addLpSearchCondition) {
            $params = $this->fillLpConditions($params);
        } elseif (!empty($this->status)) {
            $params = $this->fillStatus(
                $params,
                $this->status
            );
        }



        if (!empty($this->blockReason)) {
            $params = $this->fillBlockReason($params, $this->blockReason);
        }


        /*Fill params array from search params*/
        if (!empty($this->subjects)) {
            $params = $this->fillSubject(
                $params,
                (array)$this->subjects,
                $this->subjectCompareCondition ?? static::COMPARE_CONDITION_OR
            );
        } elseif ($this->jobToCompare) {
            $params = $this->fillSubject(
                $params,
                array_keys($this->jobToCompare->getRelatedSubjectsWithSubjectsFromCategories())
            );
        }

        if (!empty($this->fullName)) {
            $params = $this->fillFullName($params, $this->fullName);
        }

        if (!empty($this->address)) {
            $params = $this->fillAddress($params, $this->address);
        }

        if ($this->receiveNewJobPostedNotifications) {
            $params = $this->fillSendNewJobPostedNotifications($params, $this->receiveNewJobPostedNotifications);
        }

        if (!empty($this->gender)) {
            $this->gender = strtolower($this->gender);
        }
        if (!empty($this->gender) && $this->gender != 'b') {
            $params = $this->fillGender($params, $this->gender);
        } elseif ($this->jobToCompare && strtolower($this->jobToCompare->gender) != 'b') {
            $params = $this->fillGender($params, $this->jobToCompare->gender);
        }

        if (!empty($this->cityId)) {
            $params = $this->fillCity($params, $this->cityId);
        }

        if (!empty($this->stateName)) {
            $params = $this->fillState($params, $this->stateName);
        }

        if ($this->excludeHiddenProfiles) {
            $params = $this->fillHideProfile($params);
        }

        if ($this->selectNewTutors) {
            $params = $this->fillDateCreate($params, '-30 days');
        }

        if ($this->minimalRating) {
            $params = $this->fillMinimalRating($params, $this->minimalRating, true);
        }

        if (!empty($this->fromRate) || !empty($this->toRate)) {
            $params = $this->fillRate($params, $this->fromRate ? (int)$this->fromRate : null, $this->toRate ? (int)$this->toRate : null);
        } elseif ($this->jobToCompare) {
            $params = $this->fillRate(
                $params,
                $this->jobToCompare->hourlyRateFrom ? (int)$this->jobToCompare->hourlyRateFrom : null,
                $this->jobToCompare->hourlyRateTo ? (int)$this->jobToCompare->hourlyRateTo : null,
                $this->jobToCompare->account->company->commission ?? null
            );
        }

        if (!empty($this->fromAge) || !empty($this->toAge)) {
            if ($this->fromAge == $this->toAge) {
                $from = date('Y-m-d', strtotime('-' . $this->fromAge . ' year'));
                $to = date('Y-m-d', strtotime('-' . ($this->toAge + 1) . ' year'));
                $params = $this->fillAgeParams($params, $from, $to);
            } else {
                $from = date('Y-m-d', strtotime('-' . $this->fromAge . ' year'));
                $to = date('Y-m-d', strtotime('-' . ($this->toAge) . ' year'));
                $params = $this->fillAgeParams($params, $from, $to);
            }
        }

        if (
            !empty(Yii::$app->session)
            && !Yii::$app->session->has(static::SESSION_KEY_NO_TUTORS)
            && (
                empty($this->zipCode)
                && $this->_useCurrentUserLocation
            )
            && !$this->jobToCompare
            && !\Yii::$app->isApiApp()
        ) {
            $zipCode = ZipCodeHelper::getZipCodeByUserId();
            $this->zipCode = $zipCode === false ? null : $zipCode;
        } elseif ($this->jobToCompare && !$this->jobToCompare->isOnline) {
            $this->zipCode = $this->jobToCompare->zipCode;
        }

        $zipcodeLocation = Location::getZipcodeLocation($this->zipCode);
        /* Zip-code search params*/
        if (!empty($this->zipCode)) {
            if ($zipcodeLocation) {
                $this->zipCodeLocation = $zipcodeLocation;

                $params = $this->fillDistance($params, $this->distance, $this->zipCodeLocation['latitude'], $this->zipCodeLocation['longitude']);
                $this->setDistanceSort($this->zipCodeLocation['latitude'], $this->zipCodeLocation['longitude']);

                if (!isset($addparams['page']) || $addparams['page'] == 1) {
                    $firstPage = true;
                    $pageSize = $this->isRotateSortExcluded() || $this->totalTutors < $this->getCountOnPage($pageSize) ? $pageSize : $this->getExcludePageSize($pageSize);
                }
                $distanceScoreScript = TutorSearchHelper::generateDistanceScoreScript($this->zipCodeLocation['latitude'], $this->zipCodeLocation['longitude']);
                $params = $this->setParamsFunctions($params, $distanceScoreScript);
                $params['boost_mode'] = 'replace';

                $newParams['function_score'] = $params;
                $params = $newParams;
            } else {
                array_push($params['query']['bool']['must'], [
                    'regexp' => [
                        'zipCode' => $this->zipCode . '.*',
                    ],
                ]);
            }
        }

        //sort by last-visit
        if (empty($params['function_score']['functions'])) {
            $params['function_score']['functions'] = [];
        }

        array_push($params['function_score']['functions'], TutorSearchHelper::generateLastVisitScoreScript());
        array_push($params['function_score']['functions'], TutorSearchHelper::generateHoursPerRelationScoreScript());

        //compare tutor's availability if job was set
        if ($this->jobToCompare && $this->jobToCompare->availability) {
            array_push($params['function_score']['functions'], TutorSearchHelper::generateTutorAvailabilityScript($this->jobToCompare));
        }

        //add tutor's total score to score calculation
        array_push($params['function_score']['functions'], TutorSearchHelper::generateAddTotalScoreScript());


        $queryArray = [];
        //query array and functions array must be on the same level in function_score array
        if (!empty($params['function_score']) || !empty($params['functions'])) {
            $params['function_score']['score_mode'] = 'sum';
            $params['function_score']['boost_mode'] = 'replace';
            $queryArray['function_score'] = ArrayHelper::remove($params, 'function_score');
            if (!empty($params['query']) && is_array($params['query'])) {
                $queryArray['function_score']['query'] = array_merge(
                    $queryArray['function_score']['query'] ?? [],
                    ArrayHelper::remove($params, 'query')
                );
            }
            if (!empty($params['functions']) && is_array($params['functions'])) {
                $queryArray['function_score']['functions'] = ArrayHelper::remove($params, 'functions');
            }
        } else {
            $queryArray = ArrayHelper::remove($params, 'query');
        }
        $params = $queryArray + $params;


        if ($this->excludedTutorsIds) {
            $params = $this->excludeTutorsById($params, $this->excludedTutorsIds);
        }

        //include tutor's option "hide on marketplace"
        if ($this->excludeHiddenSearch) {
            $params = $this->excludeHiddenFromSearch($params);
        }

        $this->lastSearchParams = $params;
        $query = self::find()->query($params);

        $this->setSortScoreAsc();
        $sort = $this->sort($addparams, ['_score' => SORT_DESC]);
        /* Exclude tutors settings*/
        if (!empty($this->zipCodeLocation) || ($this->jobToCompare && $this->jobToCompare->isOnline)) {
            if ($this->isRotateSortExcluded()) {
                $newPageSize = $this->totalTutors > $this->getCountOnPage($pageSize) ? $pageSize : $this->getCountOnPage($pageSize);
            } else {
                $newPageSize = $pageSize;
            }

            if ($this->isRotateSortExcluded()) {
                $this->addMultiSort('_score');
            }

            if (!empty($this->zipCodeLocation)) {
                $this->setDistanceCalc($query);
            }
        } else {
            $newPageSize = $pageSize;
            /*$this->setSortTotalScoreAsc();
            if ($this->isRotateSortExcluded()) {
                $this->addMultiSort('totalScore');
            }
            $sort = $this->sort($addparams);*/
        }

        if (!empty($this->returnedClass)) {
            ElasticActiveDataProvider::$returnedClass = $this->returnedClass;
        }

        $provider = new ElasticActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $newPageSize,
                //look like 0 is index of first page
                'page' => $this->page - 1,
                'params' => $addparams
            ],
            'sort' => $sort,
        ]);

        if ($provider->getTotalCount() <= static::SMALL_RESULT_POP_UP_LIMIT && !empty(Yii::$app->session)) {
            Yii::$app->session->set(TutorSearch::SESSION_KEY_NO_TUTORS, true); /* No result by user search params*/
        }
        /*ADDING TUTORS*/
        if ($this->addingTutors) {
            $ids = [];
            $realCount = $provider->getTotalCount(); /*Get real result count*/
            if ($realCount) {
                foreach ($provider->getModels() as $model) {
                    $ids[] = (int)$model->getPrimaryKey();
                }
            }
            /*Script that moves Tutors from "real result" to top */
            $sortScript = $this->getSortScriptAddTutor($ids);
            $params_default = [
                'function_score' => [
                    'boost_mode' => 'replace',
                    'functions' => $params['functions'] ?? [],
                ],
            ];
            $function_score = array_merge($params_default['function_score'], self::EMPTY_SEARCH_PARAMS_ARRAY);
            if ($this->excludeHiddenProfiles) {
                $function_score = $this->fillHideProfile($function_score);
            }

            $params_default['function_score'] = $function_score;
            array_push($params_default['function_score']['functions'], $sortScript);
            if (!empty($this->subjects)) {
                $params_default['function_score'] = $this->fillSubject($params_default['function_score'], (array)$this->subjects);
            }
            $params_default['function_score'] = $this->fillLpConditions($params_default['function_score']);

            $params = $params_default;
            $query = self::find()->query($params);

            $provider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => $newPageSize,
                    'params' => $addparams,
                ],
                'sort' => $sort,
            ]);
            $newCount = $provider->getTotalCount() > self::ADD_TO_RESULT ? $realCount + self::ADD_TO_RESULT : $provider->getTotalCount();
            $provider->setTotalCount($newCount);
        }

        if (!empty($this->zipCodeLocation) && $newPageSize == $pageSize && !$this->isRotateSortExcluded() && $this->totalTutors > $this->getCountOnPage($pageSize)) {
            $provider = $this->excludeTutors($firstPage, $provider, $params, $pageSize, $newPageSize, $addparams);
        }

        $this->saveToSession();

        return $provider;
    }

    protected function setDistanceCalc(Query $query)
    {
        $query->scriptFields['distanceCalc'] = [
            "script" => "doc['location'].planeDistanceWithDefault({$this->zipcodeLocation['latitude']}, {$this->zipcodeLocation['longitude']}, 0) * 0.00062137",
        ];
    }

    public function addMultiSort($attribute)
    {
        $this->sort['hourlyRate_asc']['sort'] = [
            'hourlyRate' => SORT_ASC,
            "{$attribute}" => SORT_DESC,
        ];
        $this->sort['hourlyRate_desc']['sort'] = [
            'hourlyRate' => SORT_DESC,
            "{$attribute}" => SORT_DESC,
        ];
        $this->sort['rating_desc']['sort'] = [
            'rating' => SORT_DESC,
            "{$attribute}" => SORT_DESC,
        ];
    }

    public function removeExcludeTutorsFromSession()
    {
        Yii::$app->session->remove('tutorsExclude');
    }

    public function setExcludeTutorsFromSession($tutors)
    {
        Yii::$app->session->set('tutorsExclude', json_encode($tutors));
    }

    public function getExcludeTutorsFromSession()
    {
        $tutorsExclude = Yii::$app->session->get('tutorsExclude');
        return json_decode($tutorsExclude, true);
    }

    public function getAccount()
    {
        $model = Yii::$app->getModule('account')->model('Account');
        return $model::findOneWithoutRestrictions(parent::getPrimaryKey());
    }

    public function getAccountProfile()
    {
        $model = Yii::$app->getModule('account')->model('Profile');
        return $model::find()
            ->andWhere(['accountId' => parent::getPrimaryKey()])
            ->limit(1)
            ->one();
    }

    public function getDataObject()
    {
        return new TutorDataObject(['accountId' => parent::getPrimaryKey()]);
    }

    public function getZipCodeLocation()
    {
        return $this->zipCodeLocation;
    }

    public function sort($addparams = [], $defaultSort = ['totalScore' => SORT_DESC])
    {
        if (null !== $this->_sort) {
            return $this->_sort;
        }

        $attributes = [];
        foreach ($this->sort as $attribute) {
            $name = $attribute['attribute'];
            $direction = ($attribute['direction'] === SORT_ASC) ? 'asc' : 'desc';


            if (!empty($attribute['sort'])) {
                $sort = $attribute['sort'];
            } else {
                $sort = [
                    $name => $attribute['direction'],
                ];
            }

            $attributes[$name][$direction] = $sort;
        }

        $sort = new Sort();
        $sort->enableMultiSort = true;
        $sort->defaultOrder = $defaultSort;
        $sort->attributes = $attributes;
        $sort->params = $addparams;

        return $sort;
    }

    public function getCustomAttributes()
    {
        return [
            'distance' => $this->distance,
            'fromAge' => $this->fromAge,
            'toAge' => $this->toAge,
            'fromRate' => $this->fromRate,
            'toRate' => $this->toRate
        ];
    }

    public function forDropDown($data = [], $defaultSort = [])
    {
        if (!empty($defaultSort)) {
            $sort = $this->sort($data, $defaultSort);
        } else {
            $sort = $this->sort($data);
        }
        $attributeOrders = $sort->getAttributeOrders();

        $attributes = [];
        foreach ($this->sort as $attribute) {
            $name = $attribute['attribute'];
            $direction = $attribute['direction'];
            $label = $attribute['label'];
            $param = (($direction === SORT_ASC) ? '' : '-') . $name;
            $attributes[$label] = $this->attributes + [$sort->sortParam => $param]
                + $this->customAttributes;

            if (
                isset($attributeOrders[$name])
                && ($attributeOrders[$name] === $direction)
            ) {
                $attributes['default'] = $label;
            }
        }

        if (!isset($attributes['default'])) {
            $attributes['default'] = reset($this->sort)['label'];
        }

        return $attributes;
    }

    public function formName()
    {
        return '';
    }

    public function saveToSession()
    {
        if (empty(Yii::$app->session)) {
            return;
        }
        // TODO: Get rid of direct session component usage
        Yii::$app->session->set(self::SESSION_KEY . $this->getSearchSessionKey(), $this->attributes + $this->customAttributes);
    }

    /**
     * Hack for Tutor profile page
     * @param $keyIndex
     * @return null
     */
    public static function getSubjectIdBySessionKey($keyIndex)
    {
        $params = Yii::$app->session->get($keyIndex);
        $params = json_decode($params, true);
        return $params['subjects'] ?? null;
    }

    /**
     * Hack for Tutor profile page (after student sign up). Need for show subject description
     * @param $keyIndex
     * @return null
     */
    public static function getSignUpSubjectIdBySessionKey($keyIndex)
    {
        $params = Yii::$app->session->get($keyIndex);
        $params = json_decode($params, true);
        return $params['signUpSubjects'] ?? null;
    }

    public static function getZipCodeBySessionKey($keyIndex)
    {
        $params = Yii::$app->session->get($keyIndex);
        $params = json_decode($params, true);
        return $params['zipCode'] ?? null;
    }

    public function getSearchSessionKey()
    {
        if ($this->sessionKey === null) {
            $this->sessionKey = Yii::$app->security->generateRandomString(4);
        }
        return $this->sessionKey;
    }

    public function resetFields()
    {
        $this->fromAge = null;
        $this->toAge = null;
        $this->fromRate = null;
        $this->toRate = null;
        $this->gender = null;
        $this->zipCode = null;
        $this->distance = '50mi';
        $this->subjects = null;
    }

    public function getData()
    {
        $accountSetting = Yii::$app->getModule('account');
        return [
            'subjects' => $this->subjects ?? null,
            'zipCode' => $this->zipCode ?? null,
            'distance' => $this->distance,
            'fromRate' => $this->fromRate ?? $accountSetting->hourlyRateMinShowOnSearch,
            'toRate' => $this->toRate ?? $accountSetting->hourlyRateMaxShowOnSearch,
            'gender' => $this->gender ?? 'b',
            'fromAge' => $this->fromAge ?? $accountSetting->ageMin,
            'toAge' => $this->toAge ?? $accountSetting->ageMax,
        ];
    }
}

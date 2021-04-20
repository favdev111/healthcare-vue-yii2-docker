<?php

namespace common\helpers;

use modules\account\models\Job;
use modules\account\models\TutorScoreSettings;
use modules\payment\Module;
use yii\helpers\Json;

class TutorSearchHelper
{
    private static $distanceScoreArray;
    private static $lastVisitParamsArray;
    private static $availabilityScoreArray;
    private static $hoursPerRelationScoreArray;
    private static $_db;

    const DISTANCE_SCORE_SCRIPT_ID = 'distance-score';
    const DISTANCE_SCORE_SCRIPT = 'def distance = doc[\'location\'].planeDistanceWithDefault(params.lat, params.lng, 0) * 0.00062137;
                        distance = Math.round(distance);
                        def result = 0;
                        for(def i = 0; i < params.count; i++) {
                            def key = params.keys[i];
                            def value = Integer.parseInt(params.values[i]);
                            def indexOfDelimiter = key.indexOf(params.delimiter);
                            if(indexOfDelimiter > 0){
                                def part1 = Integer.parseInt(key.substring(0, indexOfDelimiter));
                                def part2 = Integer.parseInt(key.substring(indexOfDelimiter + 1));
                                if((part1 <= distance) && (distance <= part2)) {
                                    result = value;
                                    break;
                                }
                            } else if(key.indexOf(params.moreThanDelimiter) >= 1) {
                                key = Integer.parseInt(key.substring(0, key.length() - 1));
                                if (distance >= key) {
                                    result = value;
                                }
                            } else if (key == distance) {
                                result = value;
                            }
                        }
                        return result;';

    const LAST_VISIT_SCORE_SCRIPT_ID = 'last-visit-score';
    const LAST_VISIT_SCORE_SCRIPT = 'def hours = (int)((params.currentTimestamp - (doc[\'lastVisit\'].value ?: 0))/60/60);
                        def result = 0;
                        for(def i = 0; i < params.count; i++) {
                            def key = params.keys[i];
                            def value = Integer.parseInt(params.values[i]);
                            def indexOfDelimiter = key.indexOf(params.delimiter);
                            if(indexOfDelimiter > 0){
                                def part1 = Integer.parseInt(key.substring(0, indexOfDelimiter));
                                def part2 = Integer.parseInt(key.substring(indexOfDelimiter + 1));
                                if((part1 <= hours) && (hours <= part2)) {
                                    result = value;
                                    break;
                                }
                            } else if(key.indexOf(params.moreThanDelimiter) >= 1) {
                                key = Integer.parseInt(key.substring(0, key.length() - 1));
                                if (hours >= key) {
                                    result = value;
                                }
                            } else if (key == hours) {
                                result = value;
                            }
                        }
                        return result;';

    const COMPANY_HOURLY_RATE_SCRIPT_ID = 'company-hourly-rate';
    /**
     * Script params :
     * companyCommission
     * min
     * max
     */
    const COMPANY_HOURLY_RATE_SCRIPT = 'def rateWithCommission = doc[\'clearHourlyRate\'].value * ((100.0 / (100 - (' . Module::DEFAULT_COMMISSION . ' - params.companyCommission))));
    return (rateWithCommission >= params.min) && (rateWithCommission <= params.max);';

    const TUTOR_AVAILABILITY_SCORE_SCRIPT_ID = 'tutor-availability-score-script';
    /**
     * Script params :
     * jobAvailability
     * delimiter
     * values
     * keys
     * countAll  - count availabilities bytes
     */
    const TUTOR_AVAILABILITY_SCORE_SCRIPT = <<<SCRIPT
def result = 0;
def matchAvailabilityString = Long.toBinaryString((doc['availability'].value & params.jobAvailability));
def temp = matchAvailabilityString.replace('1', "");
def countMatch = (matchAvailabilityString.length() - temp.length());
def percent = 0;
if (params.countAll != 0) {
    percent = (float)countMatch / (float)params.countAll * 100;
}
percent = Math.round(percent);
for(def i = 0; i < params.count; i++) {
    def key = params.keys[i];
    def value = Integer.parseInt(params.values[i]);
    def indexOfDelimiter = key.indexOf(params.delimiter);
    if(indexOfDelimiter > 0){
        def part1 = Integer.parseInt(key.substring(0, indexOfDelimiter));
        def part2 = Integer.parseInt(key.substring(indexOfDelimiter + 1));
        if((part1 <= percent) && (percent <= part2)) {
            result = value;
            break;
        }
    } else {
        result = value;
    }
}
return result;
SCRIPT;

    const ADD_TOTAL_SCORE_SCRIPT_ID = 'add-total-score';
    const ADD_TOTAL_SCORE_SCRIPT = "return doc['totalScore'].value;";

    const HOURS_PER_RELATION_SCRIPT_ID = 'hours-per-relation';
    const HOURS_PER_RELATION_SCRIPT = <<<SCRIPT
                        def result = 0;
                        def HPR = (int)(doc['hoursPerRelation'].value);
                        for(def i = 0; i < params.count; i++) {
                            def key = params.keys[i];
                            def value = Integer.parseInt(params.values[i]);
                            def indexOfDelimiter = key.indexOf(params.delimiter);
                            if(indexOfDelimiter > 0){
                                def part1 = Integer.parseInt(key.substring(0, indexOfDelimiter));
                                def part2 = Integer.parseInt(key.substring(indexOfDelimiter + 1));
                                if((part1 <= HPR) && (HPR <= part2)) {
                                    result = value;
                                    break;
                                }
                            } else if(key.indexOf(params.moreThanDelimiter) >= 1) {
                                key = Integer.parseInt(key.substring(0, key.length() - 1));
                                if (HPR >= key) {
                                    result = value;
                                }
                            } else if (key == HPR) {
                                result = value;
                            }
                        }
                        return result;
SCRIPT;



    //methods
    /**
     * @return array
     */
    private static function getHoursPerRelationScoreKeys()
    {
        return array_keys(self::getHoursPerRelationScoreArray());
    }

    /**
     * @return array
     */
    private static function getHoursPerRelationScoreValues()
    {
        return array_values(self::getHoursPerRelationScoreArray());
    }

    /**
     * @return array
     */
    private static function getDistanceScoreKeys()
    {
        return array_keys(self::getDistanceScoreArray());
    }

    /**
     * @return array
     */
    private static function getDistanceScoreValues()
    {
        return array_values(self::getDistanceScoreArray());
    }

    private static function getAvailabilityScoreValues()
    {
        return array_values(self::getAvailabilityScoreArray());
    }

    private static function getAvailabilityScoreKeys()
    {
        $result = array_keys(self::getAvailabilityScoreArray());
        foreach ($result as &$item) {
            if (!is_string($item)) {
                $item = (string)$item;
            }
        }
        return $result;
    }


    //getting data for Distance Score search script
    /**
     * @return array
     */
    private static function getDistanceScoreArray()
    {
        if (empty(self::$distanceScoreArray)) {
            self::$distanceScoreArray = TutorScoreSettings::getDistanceParamsArray();
        }
        return self::$distanceScoreArray;
    }

    /**
     * @return array
     */
    private static function getHoursPerRelationScoreArray()
    {
        if (empty(self::$hoursPerRelationScoreArray)) {
            self::$hoursPerRelationScoreArray = TutorScoreSettings::getHoursPerRelationParamsArray();
        }
        return self::$hoursPerRelationScoreArray;
    }

    private static function getAvailabilityScoreArray()
    {
        if (empty(self::$availabilityScoreArray)) {
            self::$availabilityScoreArray = TutorScoreSettings::getAvailabilityParamsArray();
        }
        return self::$availabilityScoreArray;
    }

    public static function generateHoursPerRelationScoreScript()
    {
        $keys = self::getHoursPerRelationScoreKeys();
        $values = self::getHoursPerRelationScoreValues();
        $count = count($keys);

        return [
            'script_score' => [
                'script' => [
                    'id' => self::HOURS_PER_RELATION_SCRIPT_ID,
                    'params' => [
                        'count' => $count,
                        'keys' => $keys,
                        'values' => $values,
                        'delimiter' => '-',
                        'moreThanDelimiter' => '+',
                    ],
                ],
            ],
        ];
    }

    public static function generateDistanceScoreScript($lat, $lng)
    {
        $keys = self::getDistanceScoreKeys();
        $values = self::getDistanceScoreValues();
        $count = count($keys);

        return [
            'script_score' => [
                'script' => [
                    'id' => self::DISTANCE_SCORE_SCRIPT_ID,
                    'params' => [
                        'lat' => (double)$lat,
                        'lng' => (double)$lng,
                        'count' => $count,
                        'keys' => $keys,
                        'values' => $values,
                        'delimiter' => '-',
                        'moreThanDelimiter' => '+',
                    ],
                ],
            ],
        ];
    }

    public static function generateHourlyRateScript($companyCommission, $min, $max)
    {
        return [
            'script' => [
                'id' => self::COMPANY_HOURLY_RATE_SCRIPT_ID,
                'params' => [
                    'companyCommission' => $companyCommission,
                    'min' => $min,
                    'max' => $max
                ],
            ],
        ];
    }

    public static function generateLastVisitScoreScript()
    {
        $currentTimestamp = time();
        $keys = self::getLastVisitArrayKeys();
        $values = self::getLastVisitArrayValues();
        $count = count(self::getLastVisitParamsArray());

        return [
            'script_score' => [
                'script' => [
                    'id' => self::LAST_VISIT_SCORE_SCRIPT_ID,
                    'params' => [
                        'currentTimestamp' => $currentTimestamp,
                        'count' => $count,
                        'keys' => $keys,
                        'values' => $values,
                        'delimiter' => '-',
                        'moreThanDelimiter' => '+',
                    ],
                ],
            ],

        ];
    }

    //providing access to private variable (preparing groovy array)
    private static function getLastVisitParamsArray()
    {
        if (empty(self::$lastVisitParamsArray)) {
            self::$lastVisitParamsArray = TutorScoreSettings::getLastVisitParamsArray();
        }
        return self::$lastVisitParamsArray;
    }

    private static function getLastVisitArrayKeys()
    {
        return array_keys(self::getLastVisitParamsArray());
    }

    private static function getLastVisitArrayValues()
    {
        return array_values(self::getLastVisitParamsArray());
    }

    private static function getDb()
    {
        if (empty(self::$_db)) {
            self::$_db = \Yii::$app->get('elasticsearch');
        }
        return self::$_db;
    }

    public static function postDistanceScoreScript()
    {
        $db = self::getDb();
        return $db->post(
            ['_scripts', TutorSearchHelper::DISTANCE_SCORE_SCRIPT_ID],
            [],
            Json::encode(
                [
                    'script' => [
                        'lang' => 'painless',
                        'source' => TutorSearchHelper::DISTANCE_SCORE_SCRIPT,
                    ],
                ]
            )
        );
    }

    public static function postLastVisitScoreScript()
    {
        $db = self::getDb();
        return $db->post(
            ['_scripts', TutorSearchHelper::LAST_VISIT_SCORE_SCRIPT_ID],
            [],
            Json::encode(
                [
                    'script' => [
                        'lang' => 'painless',
                        'source' => TutorSearchHelper::LAST_VISIT_SCORE_SCRIPT,
                    ],
                ]
            )
        );
    }

    public static function generateAddTotalScoreScript(): array
    {
        return [
            'script_score' => [
                'script' => [
                    'id' => self::ADD_TOTAL_SCORE_SCRIPT_ID,
                ],
            ],
        ];
    }

    public static function generateTutorAvailabilityScript($job): array
    {
        $values = static::getAvailabilityScoreValues();
        $keys = static::getAvailabilityScoreKeys();
        /**
         * @var Job $job
         */
        return [
            'script_score' => [
                'script' => [
                    'id' => self::TUTOR_AVAILABILITY_SCORE_SCRIPT_ID,
                    'params' => [
                        'jobAvailability' => $job->availability,
                        'keys' => $keys,
                        'values' => $values,
                        'delimiter' => '-',
                        'count' => count($values),
                        //count available hours in job
                        'countAll' => substr_count(decbin($job->availability), 1)
                    ],
                ],
            ],

        ];
    }

    public static function postTutorAvailabilityScoreScript()
    {
        $db = self::getDb();
        return $db->post(
            ['_scripts', TutorSearchHelper::TUTOR_AVAILABILITY_SCORE_SCRIPT_ID],
            [],
            Json::encode(
                [
                    'script' => [
                        'lang' => 'painless',
                        'source' => TutorSearchHelper::TUTOR_AVAILABILITY_SCORE_SCRIPT,
                    ],
                ]
            )
        );
    }

    public static function postHourlyRateScript()
    {
        $db = self::getDb();
        return $db->post(
            ['_scripts', TutorSearchHelper::COMPANY_HOURLY_RATE_SCRIPT_ID],
            [],
            Json::encode(
                [
                    'script' => [
                        'lang' => 'painless',
                        'source' => TutorSearchHelper::COMPANY_HOURLY_RATE_SCRIPT,
                    ],
                ]
            )
        );
    }

    public static function postAddTotalScoreScript()
    {
        $db = self::getDb();
        return $db->post(
            ['_scripts', TutorSearchHelper::ADD_TOTAL_SCORE_SCRIPT_ID],
            [],
            Json::encode(
                [
                    'script' => [
                        'lang' => 'painless',
                        'source' => TutorSearchHelper::ADD_TOTAL_SCORE_SCRIPT,
                    ],
                ]
            )
        );
    }

    public static function postAddHoursPerRelationScoreScript()
    {
        $db = self::getDb();
        return $db->post(
            ['_scripts', TutorSearchHelper::HOURS_PER_RELATION_SCRIPT_ID],
            [],
            Json::encode(
                [
                    'script' => [
                        'lang' => 'painless',
                        'source' => TutorSearchHelper::HOURS_PER_RELATION_SCRIPT,
                    ],
                ]
            )
        );
    }
}

<?php

namespace modules\account\controllers\console;

use common\helpers\Location;
use common\helpers\QueueHelper;
use common\helpers\TutorSearchHelper;
use modules\account\models\AccountClientStatistic;
use modules\account\models\Subject;
use modules\account\models\SubjectSearch;
use modules\account\models\TutorScoreSettings;
use Yii;
use common\helpers\Role;
use modules\account\models\Account;
use modules\account\models\TutorSearch;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\helpers\Json;

class ElasticsearchController extends Controller
{
    public function actionRecreateAll($confirm = true)
    {
        if ($confirm && !$this->confirm('All data will be deleted?')) {
            return ExitCode::OK;
        }

        $this->actionCreateIndexSubject();
        $this->actionCreateIndexUser();
        $this->actionFillTutorType();
        $this->actionFillSubjectType();

        return ExitCode::OK;
    }

    public function actionCreateIndexSubject()
    {
        $db = SubjectSearch::getDb();
        $command = $db->createCommand();
        $command->deleteIndex(SubjectSearch::index());
        $db->put(
            [SubjectSearch::index()],
            [],
            Json::encode(
                [
                    'settings' => [
                        'analysis' => [
                            'analyzer' => [
                                'autocomplete_term' => [
                                    'tokenizer' => 'autocomplete_edge',
                                    'filter' => [
                                        'lowercase',
                                    ]
                                ],
                                'autocomplete_search' => [
                                    'tokenizer' => 'keyword',
                                    'filter' => [
                                        'lowercase',
                                    ],
                                ],
                            ],
                            'tokenizer' => [
                                'autocomplete_edge' => [
                                    'type' => 'ngram',
                                    'min_gram' => 1,
                                    'max_gram' => 100,
                                ],
                            ],
                        ],
                    ],
                    'mappings' => [
                        SubjectSearch::type() => [
                            'properties' => [
                                'name' => [
                                    'type' => 'text',
                                    'analyzer' => 'autocomplete_term',
                                    'search_analyzer' => 'autocomplete_search',
                                ],
                                'keywords' => [
                                    'type' => 'text',
                                    'analyzer' => 'autocomplete_term',
                                    'search_analyzer' => 'autocomplete_search',
                                ],
                            ],
                        ],
                    ],
                ]
            )
        );

        return ExitCode::OK;
    }

    public function actionCreateIndexUser()
    {
        $this->actionPostScripts();

        $db = TutorSearch::getDb();
        $command = $db->createCommand();
        $command->deleteIndex(TutorSearch::index());
        $db->put(
            [TutorSearch::index()],
            [],
            Json::encode(
                [
                    'mappings' => [
                        TutorSearch::type() => [
                            'properties' => [
                                'accountId' => ['type' => 'long'],
                                'gender' => ['type' => 'text'],
                                'subjects' => ['type' => 'long'],
                                'zipCode' => ['type' => 'text'],
                                'hourlyRate' => ['type' => 'long'],
                                'clearHourlyRate' => ['type' => 'double'],
                                'dateOfBirth' => ['type' => 'date'],
                                'cityId' => ['type' => 'long'],
                                'stateName' => ['type' => 'text'],
                                'location' => ['type' => 'geo_point'],
                                'rating' => ['type' => 'double'],
                                'hours' => ['type' => 'double'],
                                'responseTime' => ['type' => 'double'],
                                'contentScore' => ['type' => 'long'],
                                'hoursScore' => ['type' => 'long'],
                                'ratingScore' => ['type' => 'long'],
                                'responseTimeScore' => ['type' => 'long'],
                                'totalScore' => ['type' => 'long'],
                                'lastVisit' => ['type' => 'long'],
                                'hideProfile' => ['type' => 'byte'],
                                'availability' => ['type' => 'long'],
                                'createdAt' => ['type' => 'date'],
                                'receiveNewJobPostedNotifications' => ['type' => 'byte'],
                                'countReviews' => ['type' => 'long'],
                                'fullName' => ['type' => 'text'],
                                'address' => ['type' => 'text'],
                                'searchHide' => ['type' => 'byte'],
                                'status' => ['type' => 'long'],
                                'blockReason' => ['type' => 'long'],
                                'hoursPerRelation' => ['type' => 'double'],
                            ],
                        ],
                    ],
                ]
            )
        );

        return ExitCode::OK;
    }

    public function actionFillTutorType()
    {
        $accounts = Account::findWithoutRestrictions()
            ->andWhere([
                'and',
                ['=', 'roleId', Role::ROLE_SPECIALIST],
            ]);

        $this->stdout("Accounts list:\n", Console::BOLD);
        foreach ($accounts->each() as $account) {
            $this->stdout("{$account->id}\n", Console::BOLD);

            Yii::$app->getModule('account')->updateTutorSearchIndex($account->id);
        }

        return ExitCode::OK;
    }

    public function actionFillSubjectType()
    {
        $subjects = Subject::find();
        $this->stdout("Subjects list:\n", Console::BOLD);
        foreach ($subjects->each() as $subject) {
            $this->stdout("{$subject->id}\n", Console::BOLD);
            if (!SubjectSearch::createIndex($subject)) {
                $this->stderr('Failed to create index for subject :' . $subject->id . "\n", Console::BOLD);
            }
        }

        return ExitCode::OK;
    }

    /**
     * @param int $numberOfDays
     * @throws \yii\db\Exception
     */
    public function actionUpdateTutorsScores($numberOfDays)
    {
        $dateToCompare = (new \DateTime())
            ->modify("-$numberOfDays day")
            ->format(AccountClientStatistic::LAST_LOGIN_FORMAT);

        $resultArray = Account::find()
            ->byActiveStatus()
            ->tutor()
            ->excludeHiddenOnMarketplace()
            ->select('account.id')
            ->joinWith('clientStatistic')
            ->andWhere(['>=', 'lastVisit', $dateToCompare])
            ->orderBy('id DESC')
            ->createCommand()->queryAll();

        foreach ($resultArray as $item) {
            QueueHelper::setTutorScore($item['id']);
        }
    }

    public static function actionGetScoreInformation($zipCode, $tutorId)
    {
        $model = TutorSearch::findOne($tutorId);
        if (empty($model)) {
            Console::output('Tutor not found');
        }
        $location1 = Location::getZipcodeLocation($zipCode);
        $location2 = $model->location;
        $distance = Location::getDistance($location1['latitude'], $location1['longitude'], $location2['lat'], $location2['lon']);
        $hours = (time() - $model->lastVisit) / 3600;
        $lvScore = TutorScoreSettings::getLastLoginCoefficient($hours);
        $distanceScore = TutorScoreSettings::getDistanceScorePoints($distance);
        Console::output("id = $tutorId\ndistance between zipCode location and tutor = $distance\npoints for distance = $distanceScore\nhours from last visit = $hours\ntotal_score = $model->totalScore, lastVisitScore = $lvScore \nsum = " . ($model->totalScore + $lvScore + $distanceScore)) ;
    }

    public function actionGetDistanceTestInfo($id)
    {
        $model = TutorSearch::findOne($id);
        if (empty($model)) {
            Console::output('Tutor not found');
        }
        $hours = (time() - $model->lastVisit) / 3600;
        $lvScore = TutorScoreSettings::getLastLoginCoefficient($hours);
        Console::output("id = $id, hours from last visit = $hours, total_score = $model->totalScore, lastVisitScore = $lvScore, sum = " . ($model->totalScore + $lvScore) . "\n") ;
    }

    public function actionGetTutorTotalScore($id)
    {
        $model = TutorSearch::findOne($id);
        if (empty($model)) {
            Console::output('Tutor not found');
            return false;
        }
        Console::output($model->totalScore);
    }

    public function actionGetTutorLastVisit($id)
    {
        $model = TutorSearch::findOne($id);
        if (empty($model)) {
            Console::output('Tutor not found');
        }
        Console::output($model->lastVisit);
    }

    public function actionSetTutorLastVisit($id, $value)
    {
        $model = TutorSearch::findOne($id);
        if (empty($model)) {
            Console::output('Tutor not found');
        }
        $model->lastVisit = $value;
        Console::output($model->save(false));
    }

    public function actionGetLastVisitTestInfo($id)
    {
        $model = TutorSearch::findOne($id);
        if (empty($model)) {
            Console::output('Tutor not found');
        }
        $hours = (time() - $model->lastVisit) / 3600;
        $lvScore = TutorScoreSettings::getLastLoginCoefficient($hours);
        Console::output("id = $id, hours from last visit = $hours, total_score = $model->totalScore, lastVisitScore = $lvScore, sum = " . ($model->totalScore + $lvScore) . "\n") ;
    }

    public function actionPostScripts()
    {
        $response = TutorSearchHelper::postDistanceScoreScript();
        Console::output('POST script ' . TutorSearchHelper::DISTANCE_SCORE_SCRIPT_ID . ' ' . json_encode($response));

        $response = TutorSearchHelper::postLastVisitScoreScript();
        Console::output('POST script ' . TutorSearchHelper::LAST_VISIT_SCORE_SCRIPT_ID . ' ' . json_encode($response));

        $response = TutorSearchHelper::postHourlyRateScript();
        Console::output('POST script ' . TutorSearchHelper::COMPANY_HOURLY_RATE_SCRIPT_ID . ' ' . json_encode($response));

        $response = TutorSearchHelper::postTutorAvailabilityScoreScript();
        Console::output('POST script ' . TutorSearchHelper::TUTOR_AVAILABILITY_SCORE_SCRIPT_ID . ' ' . json_encode($response));

        $response = TutorSearchHelper::postAddTotalScoreScript();
        Console::output('POST script ' . TutorSearchHelper::ADD_TOTAL_SCORE_SCRIPT_ID . ' ' . json_encode($response));

        $response = TutorSearchHelper::postAddHoursPerRelationScoreScript();
        Console::output('POST script ' . TutorSearchHelper::HOURS_PER_RELATION_SCRIPT_ID . ' ' . json_encode($response));
    }
}

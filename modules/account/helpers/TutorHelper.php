<?php

namespace modules\account\helpers;

use common\helpers\AccountStatusHelper;
use common\helpers\QueueHelper;
use common\models\Review;
use modules\account\models\Account;
use modules\account\models\AccountClientStatistic;
use modules\account\models\AccountRating;
use modules\account\models\AccountScore;
use Yii;
use yii\data\ActiveDataProvider;

class TutorHelper
{
    public const UPDATE_SCORE_TIME_IN_MINUTES = 30;
    public const TUTORS_LANDING_URL_SESSION_KEY = 'tutorsLandingUrl';

    public static function setTutorRating($accountId)
    {
        /** @var Account $tutorModel */
        $tutorModel = static::getModel($accountId)->one();

        if (!$tutorModel) {
            Yii::warning('Account with ID ' . $accountId . ' not found');
            return;
        }

        $rating = (new \yii\db\Query())
            ->select('COUNT(*) as count,
                          SUM(articulation) as totalArticulation,
                          SUM(proficiency) as totalProficiency,
                          SUM(punctual) as totalPunctual')
            ->from('{{%review}}')
            ->where(['accountId' => $accountId, 'status' => Review::ACTIVE])
            ->andWhere(['isAdmin' => false])
            ->one();
        $accRating = $tutorModel->rating;
        if (!$accRating) {
            $accRating = new AccountRating();
            $accRating->accountId = $accountId;
        }
        if ($rating['count']) {
            $count = $rating['count'];
            $totalArticulation = round($rating['totalArticulation'] / $count, 1);
            $totalProficiency = round($rating['totalProficiency'] / $count, 1);
            $totalPunctual = round($rating['totalPunctual'] / $count, 1);
            $totalRating = round(($totalArticulation + $totalProficiency + $totalPunctual) / 3, 1);

            $accRating->totalArticulation = $totalArticulation;
            $accRating->totalProficiency = $totalProficiency;
            $accRating->totalPunctual = $totalPunctual;
            $accRating->totalRating = $totalRating;
        } else {
            $accRating->totalArticulation = 0;
            $accRating->totalProficiency = 0;
            $accRating->totalPunctual = 0;
            $accRating->totalRating = 0;
        }

        $totalTime = Yii::$app->db->createCommand('SELECT ROUND(SUM(TIME_TO_SEC(TIMEDIFF(toDate, fromDate))/3600)) as totalHours FROM lesson WHERE tutorId=' . $accountId)
            ->queryOne();
        $totalStudent = Yii::$app->db->createCommand('SELECT count(DISTINCT studentId) as count FROM lesson WHERE tutorId=' . $accountId)
            ->queryOne();
        $totals = (new \yii\db\Query())
            ->select('SUM(hours) as totalHours,
                          SUM(accounts) as totalAccounts')
            ->from(Review::tableName())
            ->where(['accountId' => $accountId, 'status' => Review::ACTIVE])
            ->one();
        $accRating->totalAccounts = $totalStudent['count'] + $totals['totalAccounts'];
        $accRating->totalHours = $totalTime['totalHours'] + $totals['totalHours'];
        if (!$accRating->save()) {
            Yii::error(
                'Failed to save calculated rating totals for tutor #'
                . $accountId
                . ' Errors: '
                . json_encode($accRating->getErrors()),
                'rating'
            );
            return;
        }

        Yii::$app->getModuleAccount()->updateTutorSearchIndex(
            $accountId,
            [
                'hours' => $accRating->totalHours,
                'rating' => (float)$accRating->totalRating,
                'countReviews' => (int)$rating['count'],
            ]
        );
    }

    public static function setTutorScore($accountId)
    {
        /** @var Account $account */
        $account = static::getModel($accountId)
            ->excludeHiddenOnMarketplace()
            ->one();

        if (!$account) {
            Yii::warning('Account with ID ' . $accountId . ' not found');
            return;
        }

        $data = new \stdClass();
        $data->hoursScore = $account->getTeachHoursScore();
        $data->ratingScore = $account->getRatingScore();
        $data->responseTimeScore = $account->getResponseTimeScore();
        $data->contentScore = $account->getContentScore();
        $data->hours = $account->getTotalTeachHours();
        $data->responseTime = $account->getAvgResponseTime();
        $data->totalScore =
            $data->hoursScore
            + $data->ratingScore
            + ($data->responseTimeScore === null ? 0 : $data->responseTimeScore)
            + $data->contentScore;

        Yii::$app->getModuleAccount()->updateTutorSearchIndex($account->id, (array)$data);

        $accountScore = $account->score;
        if (!$accountScore) {
            $accountScore = new AccountScore();
        }

        $accountScore->accountId = $account->id;
        $accountScore->hoursScore = $data->hoursScore;
        $accountScore->ratingScore = $data->ratingScore;
        $accountScore->responseTimeScore = $data->responseTimeScore;
        $accountScore->contentScore = $data->contentScore;
        $accountScore->totalScore = $data->totalScore;
        $accountScore->save(false);
    }

    protected static function getModel($accountId)
    {
        return Account::find()
            ->isSpecialist()
            ->andNonSuspended()
            ->andWhere(['id' => $accountId])
            ->limit(1);
    }

    public static function writeTutorVisit(): void
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $user = Yii::$app->user->identity;
        if (
            !$user->isTutor()
            || !($statistic = AccountClientStatistic::getUserStatistic())
        ) {
            return;
        }

        $statistic->lastVisit = date(Yii::$app->formatter->MYSQL_DATETIME, time());
        $statistic->save();

        $sessionKey = 'lastVisitUpdateTimestamp';
        $lastVisitUpdate = false;
        $lastVisitUpdateTimestamp = Yii::$app->session->get($sessionKey);
        if (!$lastVisitUpdateTimestamp) {
            $lastVisitUpdateTimestamp = time();
            $lastVisitUpdate = true;
            Yii::$app->session->set($sessionKey, $lastVisitUpdateTimestamp);
        } else {
            $diff = \DateTime::createFromFormat(
                Yii::$app->formatter->MYSQL_DATETIME,
                $statistic->lastVisit
            )->getTimestamp() - $lastVisitUpdateTimestamp;
            $diff /= 60;

            if ($diff >= static::UPDATE_SCORE_TIME_IN_MINUTES) {
                $lastVisitUpdate = true;
                Yii::$app->session->set($sessionKey, time());
            }
        }
    }

    /**
     * Get DataProvider with active, not empty reviews for tutor
     *
     * @param int $accountId
     * @param int $pageSize
     *
     * @return ActiveDataProvider
     */
    public static function getReviewActiveDataProvider(int $accountId, int $pageSize = 4): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => Review::find()
                ->joinWith([
                    'tutor' => function ($query) {
                        $query->andWhere(['not', [Account::tableName() . '.status' => AccountStatusHelper::STATUS_DELETED]]);
                    },
                ])
                ->andWhere(['accountId' => $accountId, Review::tableName() . '.status' => Review::ACTIVE])
                ->andWhere([
                    'not',
                    ['message' => ''],
                ]),
            'pagination' => [
                'pageSize' => $pageSize,
            ],
            'sort' => ['defaultOrder' => ['createdAt' => SORT_DESC]],
        ]);
    }

    /**
     * Calculate unique words from account profile data
     *
     * @param Account $account
     *
     * @return int
     */
    public static function calculateTutorProfileUniqueWords(Account $account): int
    {
        $data = array_merge(
            self::splitStringIntoWords($account->getFullName()),
            self::splitStringIntoWords($account->profile->title),
            self::splitStringIntoWords($account->profile->description),
            self::splitStringIntoWords($account->profile->city->name)
        );

        foreach ($account->educations as $education) {
            $data += self::splitStringIntoWords($education->college->fullName);
        }

        $categorySubject = $account->getCategorySubject();
        foreach ($categorySubject->categories as $category) {
            $data += self::splitStringIntoWords($category->name);
            foreach ($categorySubject->subjects[$category->id] as $subject) {
                $data += self::splitStringIntoWords($subject->name);
            }
        }

        $dataProvider = TutorHelper::getReviewActiveDataProvider($account->id);
        foreach ($dataProvider->getModels() as $review) {
            $data += self::splitStringIntoWords($review->message);
        }

        return count(array_unique($data));
    }

    protected static function splitStringIntoWords(string $string): array
    {
        return str_word_count($string, 1);
    }
}

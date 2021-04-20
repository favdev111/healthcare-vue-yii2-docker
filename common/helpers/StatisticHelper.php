<?php

namespace common\helpers;

use modules\account\models\Account;
use modules\account\models\AccountClientStatistic;
use modules\account\models\AccountCompanyStatistic;
use modules\account\models\AccountWithDeleted;
use modules\account\models\Lesson;
use modules\payment\models\Transaction;

class StatisticHelper
{
    public static function updateLastLessonDate()
    {
        $clients = AccountWithDeleted::find()->isPatient()->with('clientStatistic');
        foreach ($clients->each() as $client) {
            /**
             * @var AccountWithDeleted $client
             */
            $statistic = $client->clientStatistic;
            if (!$statistic) {
                $statistic = new AccountClientStatistic([
                    'accountId' => $client->id,
                ]);
            }

            $lastLessonDate = Lesson::find()
                ->select('toDate')
                ->andWhere(['studentId' => $client->id])
                ->orderBy('id DESC')
                ->limit(1)
                ->column();

            if (!empty($lastLessonDate)) {
                $statistic->lastLessonDate = $lastLessonDate[0];
                $statistic->save(false);
            }
        }
    }

    public static function updateBilledHours()
    {
        AccountCompanyStatistic::deleteAll();
        $companies = Account::find()->isCrmAdmin()->all();
        foreach ($companies as $company) {
            //list client of company
            $clientIds = Account::findWithoutRestrictions()
                ->select('id')
                ->isPatient()
                ->column();

            $lessonsQuery = Lesson::find()->andWhere(['studentId' => $clientIds]);

            // key - id of tutor, value - count of billed hours
            $tutors = [];
            foreach ($lessonsQuery->each() as $lesson) {
                /**
                 * @var Lesson $lesson
                 */
                $tutorId = $lesson->tutorId;
                if (!isset($tutors[$tutorId])) {
                    $tutors[$tutorId] = 0;
                }
                $tutors[$tutorId] += AccountCompanyStatistic::datesDiffToDecimalHours($lesson->toDate, $lesson->fromDate);
            }

            if (!empty($tutors)) {
                foreach ($tutors as $tutorId => $billedHours) {
                    $statistic = new AccountCompanyStatistic();
                    $statistic->accountId = $company->id;
                    $statistic->tutorId = $tutorId;
                    $statistic->hoursBilled = $billedHours;
                    $statistic->save(false);
                }
            }
        }
    }

    public static function updateTotalEarned(): int
    {
        $lessonQuery = Lesson::find()->joinWith('lastCharge')->andWhere([Transaction::tableName() . '.status' => Transaction::STATUS_SUCCESS]);
        // key - id of tutor, value - totalEarned
        $tutors = [];
        foreach ($lessonQuery->each() as $lesson) {
            /**
             * @var Lesson $lesson
             */
            $tutorId = $lesson->tutorId;
            if (!isset($tutors[$tutorId])) {
                $tutors[$tutorId] = 0;
            }
            $tutors[$tutorId] += $lesson->amount;
        }

        $count = 0;
        if (!empty($tutors)) {
            foreach ($tutors as $tutorId => $totalEarned) {
                $count += \Yii::$app->db->createCommand()
                    ->update(
                        AccountClientStatistic::tableName(),
                        ['totalEarned' => $totalEarned],
                        ['accountId' => $tutorId]
                    )
                    ->execute();
            }
        }
        return $count;
    }
}

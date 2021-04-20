<?php

namespace common\helpers;

use modules\account\models\Account;
use modules\account\models\AutomatchHistory;
use modules\account\models\Job;
use modules\account\models\JobApply;

class Automatch
{
    public const QUEUE_DELAY = 60 * 60 * 3;
    public const APPLY_BONUS_KEY_SUBJECT = 'subject';
    public const APPLY_BONUS_KEY_TUTORED_ONLINE = 'tutoredOnlineBefore';

    public static $applyBonusKeys = [self::APPLY_BONUS_KEY_SUBJECT, self::APPLY_BONUS_KEY_TUTORED_ONLINE];
    public static $applyBonusLabels = [
        self::APPLY_BONUS_KEY_SUBJECT => 'Subject',
        self::APPLY_BONUS_KEY_TUTORED_ONLINE => 'Tutored online before',
    ];

    public static function getBonusPointValue(string $key)
    {
        return \Yii::$app->settings->get('automatch', $key);
    }

    public static function setBonusPointsValue($key, $value)
    {
        return \Yii::$app->settings->set('automatch', $key, $value);
    }

    public static function calculatePoints(JobApply $jobApply): array
    {
        $points = [];
        $applicant = $jobApply->account;

        $subjects = $jobApply->job->getRelatedSubjectsWithSubjectsFromCategories();
        $subjectIds = [];
        foreach ($subjects as $subject) {
            $subjectIds[] = $subject->id;
        }

        $points['ratingScore'] = $applicant->getRatingScore();
        $points['hoursScore'] = $applicant->getTeachHoursScore();
        $points['contentScore'] = $applicant->getContentScore();
        $points['hoursPerRelationScore'] = $applicant->clientStatistic->calculateHoursPerRelationPoints();
        $points['rematchesPerMatch'] = $applicant->clientStatistic->calculateRematchesPerMatchPoints();
        $points['refundsPerMatch'] = $applicant->clientStatistic->calculateRefundsPerMatchPoints();
        $points['hoursPerSubject'] = $applicant->clientStatistic->calculateHoursPerSubjectPoints($subjectIds);
        $points['bonusPoints'] = $jobApply->calculateAutomatchBonusPoints();

        $points['total'] = array_sum($points);
        return $points;
    }

    public static function isAutomatchCompany($companyId): bool
    {
        return in_array($companyId, static::companies());
    }
    public static function companies()
    {
        return \Yii::$app->settings->get('automatch', 'companies') ?? [];
    }

    public static function setCompanies($ids)
    {
        \Yii::$app->settings->set('automatch', 'companies', $ids);
    }

    public static function findMatch(Job $job): ?Account
    {
        $results = [];
        $applicants = [];
        /**
         * @var JobApply $jobApply
         * @var Account $applicant
         */
        foreach ($job->notDeclinedApplies as $jobApply) {
            $applicants[$jobApply->account->id] = $jobApply->account;
            $results[$jobApply->account->id] = $jobApply->automatchScore;
        }

        $maxScore = max($results);
        $applicantId = array_search($maxScore, $results);
        $best = $applicants[$applicantId] ?? null;
        if ($best) {
            $history = new AutomatchHistory();
            $history->jobId = $job->id;
            $history->data = $results;
            $history->matchedTutor = $applicantId;
            $history->save(false);
        }
        return $best;
    }
}

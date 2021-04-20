<?php

namespace modules\account\helpers;

use modules\account\models\Account;
use modules\account\models\Job;
use modules\account\models\Subject;
use yii\db\ActiveQuery;

class SubjectHelper
{
    public static function intersectSubjects(Account $tutor, Account $student)
    {
        $tutorSubjects = Subject::find()->select('subjectId, name')
            ->leftJoin('account_subject', 'account_subject.subjectId=subject.id')
            ->andWhere(['accountId' => $tutor->id])
            ->asArray()
            ->indexBy('name')
            ->all();
        if (!$tutorSubjects) {
            return [];
        }
        $tutorSubjectIds = array_column($tutorSubjects, 'subjectId', 'name');
        $subjects = Subject::find()->leftJoin('job_subject', 'subject.id=job_subject.subjectId')
            ->leftJoin('job', 'job.id=job_subject.jobId')
            ->andWhere(['job.accountId' => $student->id])
            ->andWhere(['close' => 0])
            ->indexBy('name')
            ->asArray()
            ->all();
        if (!$subjects) {
            return [];
        }
        $studentSubjectIds = array_column($subjects, 'id', 'name');
        $intersectSubjects = array_intersect($tutorSubjectIds, $studentSubjectIds);
        return $intersectSubjects;
    }

    /**
     * Return list of Subjects or Subject Query
     *
     * @param Account $tutor
     * @param string $searchQuery
     * @param int|null $jobId
     * @param bool $returnQuery
     * @return array|ActiveQuery|null
     */
    public static function getAccountSubjects(
        Account $tutor,
        string $searchQuery,
        int $jobId = null,
        bool $returnQuery = false
    ) {
        $subjects = null;
        if ($jobId) {
            $job = Job::findOne($jobId);
            $subjects = $tutor->getTutorSubjectsOrCategories(
                $searchQuery,
                $job->getJobSubjectsIdsSubject(),
                $job->getJobSubjectsIdsCategory(),
                $returnQuery
            );
        } else {
            $subjects = $tutor->getTutorSubjectsOrCategories(
                $searchQuery,
                null,
                null,
                $returnQuery
            );
        }

        return $subjects;
    }
}

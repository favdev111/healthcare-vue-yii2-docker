<?php

namespace console\components\queueJobs;

use common\models\Zipcode;
use modules\account\helpers\EventHelper;
use modules\account\models\backend\Account;
use modules\account\models\Job;
use modules\account\models\JobSubject;
use modules\account\models\search\JobSearch;
use Yii;
use yii\base\BaseObject;
use yii\db\Expression;
use yii\queue\RetryableJobInterface;

/**
 * Class TutorJobNotifyJob
 * @package console\components\queueJobs
 */
class TutorJobNotifyJob extends BaseObject implements RetryableJobInterface
{
    /**
     *
     */
    const DISTANCE_FOR_ONLINE_LESSONS = -1;

    /**
     * @var int $tutorId
     */
    public $tutorId;

    /**
     * @inheritDoc
     */
    public function execute($queue): bool
    {
        if (empty($this->tutorId)) {
            throw new \Exception('Tutor is empty.');
        }
        $account = Account::findOne($this->tutorId);

        if ($account->createdAt < Yii::$app->params['notificationStartedFrom']) {
            return true;
        }

        $profile = $account->profile;

        //tutor's subjects
        $tutorSubjects = $account->getSubjects()->select('subjectId')->asArray()->column();
        //categories of tutor's subjects
        $tutorCategories = array_keys($account->getCategorySubject()['categories']);

        $query = Job::find()
            ->andWhere(['close' => false])
            ->joinWith('subjects');

        $query->andWhere(
            [
                'or',
                [
                    'and',
                    [JobSubject::tableName() . '.subjectId' => $tutorSubjects],
                    [JobSubject::tableName() . '.isCategory' => false]
                ],
                [
                    'and',
                    [JobSubject::tableName() . '.subjectId' => $tutorCategories],
                    [JobSubject::tableName() . '.isCategory' => true]
                ],
            ]
        );

        if ($profile->latitude && $profile->longitude) {
            $distanceQuery = '( 3959 * acos( cos( radians(' . $profile->latitude . ') ) *
             cos( radians( ' . Zipcode::tableName() . '.latitude ) ) *
              cos( radians(' . Zipcode::tableName() . '.longitude) - radians(' . $profile->longitude . ')) +
               sin(radians(' . $profile->latitude . ')) * sin( radians(' . Zipcode::tableName() . '.latitude))))';

            $query->joinWith('zipCodeItem')
                ->addSelect(Job::tableName() . '.*')
                ->addSelect(Zipcode::tableName() . '.latitude')
                ->addSelect(Zipcode::tableName() . '.longitude')
                ->addSelect(
                    new Expression(
                        'IF (lessonOccur = ' . Job::LESSON_OCCUR_ONLINE . ', ' .
                        static::DISTANCE_FOR_ONLINE_LESSONS . ', ' . $distanceQuery . ' ) as distance'
                    )
                );
            $query->andHaving(['<=', 'distance', JobSearch::DEFAULT_MILES_VALUE]);
        } else {
            $query->addSelect(Job::tableName() . '.*')
                ->addSelect(new Expression('null as distance'));
        }

        $query->andWhere(['job.status' => Job::PUBLISH]);

        if ($rate = $account->rate) {
            $query->andWhere([
                'and',
                ['<=', 'hourlyRateFrom', $rate->hourlyRate],
                ['>=', 'hourlyRateTo', $rate->hourlyRate],
            ]);
        }

        $query
            ->andWhere(['>=', Job::tableName() . '.createdAt', Yii::$app->params['notificationStartedFrom']])
            ->andWhere(['in', Job::tableName() . '.gender', [$profile->gender, 'B']])
            ->andWhere(['and', ['<=', 'ageFrom', $profile->age], ['>=', 'ageTo', $profile->age]]);

        foreach ($query->each() as $job) {
            EventHelper::newTutorJob($account, $job);
        }
        return true;
    }


    /**
     * @return int
     */
    public function getTtr(): int
    {
        return 900;
    }


    /**
     * @param int $attempt
     * @param \Exception|\Throwable $error
     * @return bool
     */
    public function canRetry($attempt, $error): bool
    {
        return true;
    }
}

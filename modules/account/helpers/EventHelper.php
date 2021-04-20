<?php

namespace modules\account\helpers;

use modules\notification\Module as NotificationModule;
use modules\account\events\HourlyRateChangeEvent;
use modules\account\events\RequiredFieldsEdited;
use modules\account\events\SubjectChangeEvent;
use modules\account\events\RatingChangeEvent;
use modules\account\events\AvatarChangeEvent;
use modules\account\events\ChangeEvent;
use modules\account\models\Account;
use yii\base\Module as BaseModule;
use modules\account\models\Job;
use modules\account\Module;
use yii\base\Event;
use Yii;

/**
 * Class EventHelper
 * @package modules\account\helpers
 */
class EventHelper
{
    /**
     * @param $model
     * @param $insert
     * @param $changedAttributes
     */
    public static function changeLessonEvent($model, $insert, $changedAttributes): void
    {
        Event::trigger(
            Module::class,
            Module::EVENT_LESSON_CHANGE,
            new ChangeEvent([
                'model' => $model,
                'insert' => $insert,
                'changedAttributes' => $changedAttributes,
            ])
        );
    }

    /**
     * @param $model
     * @param $insert
     * @param $changedAttributes
     */
    public static function changeAccountEvent($model, $insert, $changedAttributes): void
    {
        self::triggerEvent(
            Module::EVENT_ACCOUNT_CHANGE,
            new ChangeEvent([
                'model' => $model,
                'insert' => $insert,
                'changedAttributes' => $changedAttributes,
            ])
        );
    }

    /**
     * @param $model
     * @param $insert
     * @param $changedAttributes
     */
    public static function changeProfileEvent($model, $insert, $changedAttributes): void
    {
        self::triggerEvent(
            Module::EVENT_PROFILE_CHANGE,
            new ChangeEvent([
                'model' => $model,
                'insert' => $insert,
                'changedAttributes' => $changedAttributes,
            ])
        );
    }

    /**
     * @param $model
     * @param $insert
     * @param $changedAttributes
     */
    public static function changeReviewEvent($model, $insert, $changedAttributes): void
    {
        self::triggerEvent(
            Module::EVENT_REVIEW_CHANGE,
            new ChangeEvent([
                'model' => $model,
                'insert' => $insert,
                'changedAttributes' => $changedAttributes,
            ])
        );
    }

    /**
     * @param $model
     */
    public static function deletedReviewEvent($model): void
    {
        self::triggerEvent(
            Module::EVENT_REVIEW_DELETED,
            new ChangeEvent([
                'model' => $model,
            ])
        );
    }

    /**
     * @param $accountId
     * @param $rating
     * @param $ratingOld
     */
    public static function changeRatingEvent($accountId, $rating, $ratingOld): void
    {
        self::triggerEvent(
            Module::EVENT_RATING_CHANGE,
            new RatingChangeEvent([
                'accountId' => $accountId,
                'rating' => (float)$rating,
                'ratingOld' => (float)$ratingOld,
            ])
        );
    }

    /**
     * @param $accountId
     * @param $rate
     * @param $rateOld
     * @param $rateModel
     */
    public static function changeHourlyRateEvent($accountId, $rate, $rateOld, $rateModel): void
    {
        self::triggerEvent(
            Module::EVENT_HOURLY_RATE_CHANGE,
            new HourlyRateChangeEvent([
                'accountId' => $accountId,
                'rate' => (float)$rate,
                'rateOld' => (float)$rateOld,
                'rateModel' => $rateModel,
            ])
        );
    }

    /**
     * @param $accountId
     * @param $subjectAddIds
     * @param $subjectRemoveIds
     */
    public static function changeSubjectEvent($accountId, $subjectAddIds, $subjectRemoveIds): void
    {
        self::triggerEvent(
            Module::EVENT_SUBJECT_CHANGE,
            new SubjectChangeEvent([
                'accountId' => $accountId,
                'subjectAddIds' => (array)$subjectAddIds,
                'subjectRemoveIds' => (array)$subjectRemoveIds,
            ])
        );
    }

    /**
     * @param $model
     * @param $avatarUrl
     */
    public static function changeAvatarEvent($model, $avatarUrl): void
    {
        self::triggerEvent(
            Module::EVENT_AVATAR_CHANGE,
            new AvatarChangeEvent([
                'model' => $model,
                'avatarUrl' => $avatarUrl,
            ])
        );
    }


    /**
     * @param Account $tutor
     * @param Job $job
     */
    public static function offerAcceptEvent(Account $tutor, Job $job): void
    {
        self::triggerEvent(
            NotificationModule::EVENT_OFFER_ACCEPT,
            new Event(['sender' => ['tutor' => $tutor, 'job' => $job]])
        );
    }

    /**
     * @param Account $tutor
     * @param int $chatUserId
     * @param $recipientModel
     */
    public static function replyToJobEvent(Account $tutor, int $chatUserId, $recipientModel): void
    {
        self::triggerEvent(
            NotificationModule::EVENT_REPLY_TO_JOB,
            new Event(['sender' => ['tutor' => $tutor, 'chatUserId' => $chatUserId, 'recipient' => $recipientModel]])
        );
    }

    /**
     * @param Job $job
     */
    public static function jobPostingOlder(Job $job): void
    {
        self::triggerEvent(
            NotificationModule::EVENT_JOB_POSTING_OLDER,
            new Event(['sender' => ['job' => $job]])
        );
    }

    /**
     * @param Account $tutor
     * @param Job $job
     */
    public static function newTutorJob(Account $tutor, Job $job): void
    {
        self::triggerEvent(
            NotificationModule::EVENT_NEW_TUTOR_JOB,
            new Event(['sender' => ['tutor' => $tutor, 'job' => $job]])
        );
    }

    /**
     * @param Account $client
     */
    public static function assignNewClient(Account $client): void
    {
        self::triggerEvent(
            NotificationModule::EVENT_ASSIGN_NEW_CLIENT,
            new Event(['sender' => ['client' => $client]])
        );
    }

    /**
     * @param $eventName
     * @param $event
     */
    protected static function triggerEvent($eventName, $event): void
    {
        Event::trigger(
            BaseModule::className(),
            $eventName,
            $event
        );
    }

    /**
     * Trigger event if required fields was changed
     * @param Account $account
     * @throws \yii\base\InvalidConfigException
     */
    public static function editProfileRequiredFields(Account $account): void
    {
        $module = Yii::$app->getModule('account');

        $event = Yii::createObject(RequiredFieldsEdited::class, [['account' => $account]]);
        $module->trigger(RequiredFieldsEdited::NAME, $event);
    }
}

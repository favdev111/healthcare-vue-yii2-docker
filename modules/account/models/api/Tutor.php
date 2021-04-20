<?php

namespace modules\account\models\api;

use modules\account\models\AccountCompanyStatistic;
use modules\account\models\AccountWithDeleted;
use modules\account\models\api\tutor\Education;
use modules\account\models\api\tutor\JobWithClientAndSubjects;
use modules\account\models\api\tutor\Review;
use modules\account\models\api\tutor\TutorAccountSubject;
use modules\account\models\JobApply;
use modules\account\models\query\AccountQuery;

/**
 * @inheritdoc
 *
 * @property JobHire[] $jobHires
 */
class Tutor extends \modules\account\models\Account
{
    public $distance = null;
    public $lastMessage = null;
    public $chatUserId = null;
    public $automatchScore = null;
    public $automatchDetails = null;
    //contains score calculated during elastic search process
    public $elasticScore;

    public function attributes()
    {
        return array_merge(parent::attributes(), ['elasticScore']);
    }

    /**
     * @inheritdoc
     */
    public function getProfile()
    {
        return $this->hasOne(\modules\account\models\api\tutor\Profile::className(), ['accountId' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function getApplications()
    {
        return $this->hasMany(JobApply::className(), ['accountId' => 'id'])->andOnCondition(['jobId' => Job::find()->select('id')]);
    }

    /**
     * @inheritdoc
     */
    public function getStudents()
    {
        return $this->hasMany(AccountClient::className(), ['id' => 'accountId'])->via('jobs');
    }

    /**
     * @inheritdoc
     */
    public function getJobs()
    {
        return $this->hasMany(JobWithClientAndSubjects::className(), ['id' => 'jobId'])->via('applications');
    }

    /**
     * @inheritdoc
     */
    public function getJobHires()
    {
        return $this->hasMany(JobHire::className(), ['jobId' => 'id'])->via('jobs');
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['passwordHash']);
        unset($fields['status']);
        unset($fields['banReason']);
        unset($fields['isEmailConfirmed']);
        unset($fields['roleId']);
        unset($fields['createdAt']);
        unset($fields['updatedAt']);
        unset($fields['createdIp']);
        unset($fields['searchHide']);
        unset($fields['countSendNotification']);
        unset($fields['countSendCardErrorNotification']);
        unset($fields['email']);
        $fields['hourlyRate'] = function () {
            return $this->rate->displayRate;
        };
        $fields['reviewsCount'] = 'countReview';
        $fields['avatarUrl'] = 'avatarUrl';
        $fields['profile'] = 'profile';
        $fields['totalRating'] = function () {
            return floatval($this->rating->totalRating ?? 0);
        };
        $fields['displayName'] = 'displayName';
        $fields['distance'] = 'distance';
        $fields['lastMessage'] = 'lastMessage';
        $fields['chatUserId'] = function () {
            return $this->chat->chatUserId ?? 0;
        };
        $fields['email'] = 'email';
        $fields['totalEarned'] = function () {
            return $this->clientStatistic->totalEarned ?? 0;
        };
        $fields['totalHires'] = function () {
            return intval($this->getJobHires()->count());
        };
        $fields['totalAppliances'] = function () {
            return intval($this->getApplications()->count());
        };
        $fields['elasticScore'] = 'elasticScore';
        $fields[] = 'automatchScore';
        $fields[] = 'automatchDetails';
        return $fields;
    }

    public function extraFields()
    {
        $extraFields = parent::extraFields();
        $extraFields['reviews'] = 'reviews';
        $extraFields['rate'] = 'rate';
        $extraFields['subjects'] = 'subjects';
        $extraFields['educations'] = 'educations';
        $extraFields['rating'] = 'rating';
        $extraFields['jobs'] = 'jobs';
        $extraFields['students'] = function () {
            $students = $this->students;
            foreach ($students as $student) {
                $student->lastMessage = $student->getChatLastMessage($this);
            }
            return $students;
        };
        return $extraFields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubjects()
    {
        return $this->hasMany(TutorAccountSubject::className(), ['accountId' => 'id']);
    }

    /**
     * @param $query AccountQuery
     * @return mixed
     */
    protected static function addAppliedTutorCondition($query)
    {
        $ownClientJobsQuery = Job::find()->select('id');
        return $query->isSpecialist()->joinWith('applications')->andWhere([JobApply::tableName() . '.jobId' => $ownClientJobsQuery]);
    }

    /**
     * @param $query AccountQuery
     * @return mixed
     */
    protected static function addTutorCondition($query)
    {
        return $query->isSpecialist();
    }

    /**
     * @inheritdoc
     */
    public static function findOne($id)
    {
        $query = parent::findByConditionWithoutRestrictions(['id' => $id]);
        static::addTutorCondition($query);
        return $query->one();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        $query = parent::findWithoutRestrictions();
        static::addTutorCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findByCondition($condition)
    {
        $query = parent::findByConditionWithoutRestrictions($condition);
        static::addTutorCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findBySql($sql, $params = [])
    {
        $query = parent::findBySqlWithoutRestrictions($sql, $params);
        static::addTutorCondition($query);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findAll($condition)
    {
        $query = parent::findByConditionWithoutRestrictions($condition);
        static::addTutorCondition($query);
        return $query->all();
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEducations()
    {
        return $this->hasMany(Education::className(), ['accountId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReviews()
    {
        return $this->hasMany(Review::className(), ['accountId' => 'id']);
    }
}

<?php

namespace modules\account\models\api;

use modules\account\models\AutomatchHistory;
use yii\db\ActiveQueryInterface;

/**
 * @inheritdoc
 * @property-read Account $responsible
 */
class JobHire extends \modules\account\models\JobHire
{
    public function rules()
    {
        $isCreateManual = $this->isManual && $this->isNewRecord;
        $rules = parent::rules();
        $rules['jobExistsManual'] = [
            ['jobId'],
            'exist',
            'skipOnError' => true,
            'targetClass' => Job::class,
            'targetAttribute' => ['jobId' => 'id'],
            'filter' => function ($query) {
                $query->andWhere(['close' => false]);
            },
            'when' => function () use ($isCreateManual) {
                return $isCreateManual;
            }
        ];

        $rules['jobExists'] = [
            ['jobId'],
            'exist',
            'skipOnError' => true,
            'targetClass' => Job::class,
            'targetAttribute' => ['jobId' => 'id'],
            'when' => function () use ($isCreateManual) {
                return !$isCreateManual;
            }
        ];
        $rules['statuses'] = ['status', 'in', 'range' => [self::STATUS_HIRED, self::STATUS_CLOSED_BY_COMPANY, self::STATUS_DECLINED_BY_COMPANY]];
        $rules['price'] = ['price', 'default', 'value' => function ($model) {
            return $this->tutor->rate->hourlyRate;
        }
        ];
        return $rules;
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['price'] = function () {
            return intval($this->formattedPrice);
        };
        unset($fields['tutoringHours']);
        $fields['displayedTutoringHours'] = 'displayedTutoringHours';
        return $fields;
    }

    public function extraFields()
    {
        $extraFields = parent::extraFields();
        $extraFields['job'] = 'job';
        $extraFields['tutor'] = 'tutor';
        $extraFields['student'] = 'student';
        $extraFields['responsible'] = 'responsible';
        $extraFields['jobSubjects'] = 'subjectsOrCategories';
        $extraFields['isAutomatch'] = 'isAutomatch';
        $extraFields['changeList'] = 'changeList';
        return $extraFields;
    }

    public function getIsAutomatch()
    {
        return AutomatchHistory::find()->byJob($this->jobId)->byMatchedTutor($this->tutorId)->exists();
    }
    public function getResponsible()
    {
        return $this->hasOne(AccountWithDeleted::class, ['id' => 'responsibleId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTutor()
    {
        return $this->hasOne(Tutor::className(), ['id' => 'tutorId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(AccountClient::className(), ['id' => 'accountId'])->via('job');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJob()
    {
        return $this->hasOne(Job::className(), ['id' => 'jobId']);
    }

    /**
     * @inheritdoc
     */
    public static function findOne($id)
    {
        $query = self::findByConditionWithoutRestrictions(['id' => $id]);
        return $query->one();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        $query = self::findWithoutRestrictions();
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findByCondition($condition)
    {
        $query = self::findByConditionWithoutRestrictions($condition);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findBySql($sql, $params = [])
    {
        $query = self::findBySqlWithoutRestrictions($sql, $params);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findAll($condition)
    {
        $query = self::findByConditionWithoutRestrictions($condition);
        return $query->all();
    }

    // Proxy-ing default methods as custom ones to allow getting suspended jobs too
    public static function findOneWithoutRestrictions($condition)
    {
        return parent::findOne($condition);
    }

    public static function findWithoutRestrictions()
    {
        return parent::find();
    }

    public static function findByConditionWithoutRestrictions($condition)
    {
        return parent::findByCondition($condition);
    }

    public static function findBySqlWithoutRestrictions($sql, $params = [])
    {
        return parent::findBySql($sql, $params = []);
    }

    public static function findAllWithoutRestrictions($condition)
    {
        return parent::findAll($condition);
    }
}

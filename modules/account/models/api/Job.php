<?php

namespace modules\account\models\api;

use common\helpers\Automatch;
use common\helpers\Location;
use modules\account\models\JobLead;
use modules\payment\models\interfaces\PaymentSourceInterface;
use yii\base\NotSupportedException;
use yii\db\ActiveQueryInterface;
use yii\db\Expression;

/**
 * @inheritdoc
 *
 * @property Tutor[] $applicants
 */
class Job extends \modules\account\models\Job
{
    public function rules()
    {
        $rules = [];
        $rules = array_merge(
            $rules,
            parent::rules()
        );
        $rules[] = ['accountId', 'required'];
        $rules['own_client'] = [
            'accountId',
            'exist',
            'skipOnError' => true,
            'targetClass' => AccountClient::class,
            'targetAttribute' => ['accountId' => 'id'],
        ];
        $rules[] = [['close', 'isAutomatchEnabled'], 'boolean'];
        $rules[] = ['accountId', 'processPaymentCheck'];
        array_unshift($rules, ['hourlyRateTo', 'default', 'value' => 250]);
        unset($rules['descriptionRemoveTags']);

        return $rules;
    }

    public function processPaymentCheck()
    {
        if ($this->hasErrors()) {
            return false;
        }

        /**
         * @var $account \modules\account\models\Account
         */
        $account = \modules\account\models\Account::findOne($this->accountId);

        if ($account->isCrmAdmin()) {
            $this->addError('', 'You can not create jobs for yourself. Please add jobs for your clients only');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public static function findOne($id)
    {
        $query = parent::findByConditionWithoutRestrictions(['id' => $id]);
        return $query->one();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        $query = parent::findWithoutRestrictions();
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findByCondition($condition)
    {
        $query = parent::findByConditionWithoutRestrictions($condition);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findBySql($sql, $params = [])
    {
        $query = parent::findBySqlWithoutRestrictions($sql, $params);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findAll($condition)
    {
        $query = parent::findByConditionWithoutRestrictions($condition);
        return $query->all();
    }

    // Proxy-ing default methods as custom ones to allow getting suspended jobs too
    public static function findOneWithoutRestrictions($condition)
    {
        throw new NotSupportedException();
    }

    public static function findWithoutRestrictions()
    {
        throw new NotSupportedException();
    }

    public static function findByConditionWithoutRestrictions($condition)
    {
        throw new NotSupportedException();
    }

    public static function findBySqlWithoutRestrictions($sql, $params = [])
    {
        throw new NotSupportedException();
    }

    public static function findAllWithoutRestrictions($condition)
    {
        throw new NotSupportedException();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(AccountClient::className(), ['id' => 'accountId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApplicants()
    {
        return $this->hasMany(Tutor::className(), ['id' => 'accountId'])->via('jobApply');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobHires()
    {
        return $this->hasMany(JobHire::className(), ['jobId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobOffers()
    {
        return $this->hasMany(JobOffer::className(), ['jobId' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['blamebale']);
        return $behaviors;
    }

    public function beforeSave($insert)
    {
        $isEnabled = $this->checkIsAutomatchJob();

        if ((bool)$this->isAutomatchEnabled != $isEnabled) {
            $this->isAutomatchEnabled = $isEnabled;
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return [
            'manualApplicants' => function () {
                return array_map(
                    'intval',
                    JobApply::find()
                        ->andWhere(['jobId' => $this->id])
                        ->andWhere(['isManual' => true])
                        ->select('accountId')
                        ->asArray()->column()
                );
            },
            'applicants' => function () {
                $applicants = $this->applicants;
                $zipCodeLocation = Location::getZipcodeLocation($this->zipCode);
                /**
                 * @var Tutor $applicant
                 */
                foreach ($applicants as $applicant) {
                    $applicant->lastMessage = $applicant->getChatLastMessage($this->account);
                    $applicant->distance = $applicant->profile->locationDistance(
                        $zipCodeLocation['latitude'],
                        $zipCodeLocation['longitude']
                    );
                    /**
                     * @var \modules\account\models\JobApply $apply
                     */
                    $apply = $applicant->getApplications()
                        ->andWhere(['jobId' => $this->id])
                        ->limit(1)
                        ->one();
                    $applicant->automatchScore = $apply->automatchScore ?? null;
                    $applicant->automatchDetails = $apply->automatchData ?? null;
                }
                return $applicants;
            },
            'applicantsInterested' => function () {
                $leadsQuery = JobLead::find()
                    ->from(new Expression(JobLead::tableName() . ' USE INDEX (`index_accountId-jobId`)'))
                    ->andWhere(['jobId' => $this->id]);
                $applicants = $this->applicants;
                if ($applicants) {
                    $leadsQuery->andWhere([
                        'or',
                        ['is', 'accountId', null],
                        ['not in', 'accountId', array_column($applicants, 'id')]
                    ]);
                }

                return $leadsQuery->all();
            },
            'client' => 'account',
            'chat' => function () {
                return $this->account->chat;
            },
            'jobHires' => 'jobHires',
            'jobOffers' => 'jobOffers',
            'changeList' => 'changeList',
            'latestJobOffer' => 'latestJobOffer',
            'availabilityData' => 'availabilityData',
            'subjects' => function () {
                $subjects = $this->getSubjectsOrCategories();
                return $subjects;
            },
            'coordinates' => function () {
                return $this->getCoordinates(false);
            },
            'files' => function () {
                return $this->attachedFiles;
            }
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();
        $fields['name'] = 'nameWithLocationAndSubject';
        $fields['availabilityArray'] = 'availabilityArray';
        $fields['billRate'] = function () {
            return $this->billRate ? (double)$this->billRate : '';
        };
        $fields['countProcessedBatches'] = 'countProcessedBatches';
        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['billRate'] = 'Client\'s rate';
        $attributeLabels['hourlyRateTo'] = 'Tutor\'s rate';
        return $attributeLabels;
    }
}

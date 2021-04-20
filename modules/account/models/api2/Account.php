<?php

namespace modules\account\models\api2;

use modules\account\models\AccountAccessToken;
use modules\account\responses\AccountResponse;
use yii\db\ActiveQuery;

/**
 * Class Account
 * @package modules\account\models\api2
 *
 * @property AccountTelehealthState[] $telehealthStates
 * @property AccountLicenceState[] $licenceStates
 * @property AccountEducation[] $educations
 * @property AccountReward[] $certifications
 * @property AccountRate $rate
 * @property HealthTest[] $healthTests
 * @property Symptom[] $symptoms
 * @property MedicalCondition[] $medicalConditions
 * @property AutoimmuneDisease[] $autoimmuneDiseases
 * @property HealthGoal[] $healthGoals
 * @property InsuranceCompany[] $insuranceCompanies
 * @property-read \modules\account\responses\AccountResponse|object $response
 * @property Profile $profile
 */
class Account extends \modules\account\models\Account
{
    public const SCENARIO_LOGIN = 'login';

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($tokenString, $type = null)
    {
        $tokenModel = AccountAccessToken::find()->andWhere(['token' => $tokenString])->limit(1)->one();

        if (!$tokenModel) {
            return null;
        }

        return $tokenModel->account;
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_SING_UP_SPECIALIST] = ['newPassword', 'email'];
        $scenarios[self::SCENARIO_UPDATE_SPECIALIST] = ['newPassword', 'email'];
        return $scenarios;
    }

    /**
     * @return ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(Profile::class, ['accountId' => 'id']);
    }

    public function getLicenceStates()
    {
        return $this->hasMany(AccountLicenceState::class, ['accountId' => 'id']);
    }

    public function getTelehealthStates()
    {
        return $this->hasMany(AccountTelehealthState::class, ['accountId' => 'id']);
    }

    public function getCertifications()
    {
        return $this->hasMany(AccountReward::class, ['accountId' => 'id']);
    }

    public function getInsuranceCompanies()
    {
        return $this->hasMany(InsuranceCompany::class, ['id' => 'insuranceCompanyId'])
                ->viaTable(AccountInsuranceCompany::tableName(), ['accountId' => 'id']);
    }


    public function getEducations()
    {
        return $this->hasMany(AccountEducation::class, ['accountId' => 'id']);
    }

    public function getRate()
    {
        return $this->hasOne(AccountRate::class, ['accountId' => 'id']);
    }

    public function getHealthTests()
    {
        return $this->hasMany(HealthTest::class, ['id' => 'healthTestId'])
            ->viaTable(AccountHealthTest::tableName(), ['accountId' => 'id']);
    }

    public function getSymptoms()
    {
        return $this->hasMany(Symptom::class, ['id' => 'symptomId'])
            ->viaTable(AccountSymptom::tableName(), ['accountId' => 'id']);
    }

    public function getMedicalConditions()
    {
        return $this->hasMany(MedicalCondition::class, ['id' => 'medicalConditionId'])
            ->viaTable(AccountMedicalCondition::tableName(), ['accountId' => 'id']);
    }

    public function getAutoimmuneDiseases()
    {
        return $this->hasMany(AutoimmuneDisease::class, ['id' => 'autoimmuneDiseaseId'])
            ->viaTable(AccountAutoimmuneDisease::tableName(), ['accountId' => 'id']);
    }

    public function getHealthGoals()
    {
        return $this->hasMany(HealthGoal::class, ['id' => 'healthGoalId'])
            ->viaTable(AccountHealthGoal::tableName(), ['accountId' => 'id']);
    }

    public function getLanguages()
    {
        return $this->hasMany(Language::class, ['id' => 'languageId'])
            ->viaTable(AccountLanguage::tableName(), ['accountId' => 'id']);
    }

    /**
     * @return AccountResponse|object
     * @throws \yii\base\InvalidConfigException
     */
    public function getResponse()
    {
        return \Yii::createObject(AccountResponse::class, [$this]);
    }
}

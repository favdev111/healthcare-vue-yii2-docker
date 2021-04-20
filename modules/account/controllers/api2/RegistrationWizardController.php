<?php

namespace modules\account\controllers\api2;

use common\components\UploadedFile;
use common\helpers\AccountStatusHelper;
use common\helpers\QueueHelper;
use modules\account\models\api2\Account;
use modules\account\models\api2\AccountEducation;
use modules\account\models\api2\AccountLicenceState;
use modules\account\models\api2\AccountReward;
use modules\account\models\api2\AccountTelehealthState;
use modules\account\models\api2\forms\RegistrationWizardStep1;
use common\helpers\Role;
use modules\account\models\api2\forms\RegistrationWizardStep2;
use modules\account\models\api2\forms\RegistrationWizardStep3;
use modules\account\models\api2\forms\RegistrationWizardStep4;
use modules\account\models\api2\forms\RegistrationWizardStep5;
use modules\account\models\api2\AccountRate;
use modules\account\models\api2\forms\RegistrationWizardStep6;
use modules\account\models\ar\AccountInsuranceCompany;
use modules\account\models\ar\AccountLanguage;
use modules\account\responses\AccountResponse;
use Yii;
use yii\web\ForbiddenHttpException;

/**
 * Default controller for Account actions
 */
class RegistrationWizardController extends \api2\components\RestController
{
    /**
     * @var array
     */
    protected const ALLOW_STEPS_AT_REVIEW = [
        2,
        3,
        5,
        6,
    ];

    /**
     * @param int $actionStep
     * @throws ForbiddenHttpException
     */
    protected function checkStep(int $actionStep)
    {
        $account = $this->currentAccount;

        if ($account->isUnderReview() && !in_array($actionStep, self::ALLOW_STEPS_AT_REVIEW, true)) {
            throw new ForbiddenHttpException(
                'You have already completed your registration. You can go to dashboard'
            );
        }

        if ($actionStep > $account->registrationStep) {
            throw new ForbiddenHttpException('Invalid registration step');
        }
    }

    protected function prepareData(): array
    {
        return [Yii::$app->db->beginTransaction(), Yii::$app->request->post(), Yii::$app->user->identity];
    }

    protected function processMultipleModels(?array $data, string $className): array
    {
        if (empty($data)) {
            return [];
        }
        $models = [];
        foreach ($data as $item) {
            $model = new $className();
            $model->setAttributes($item, false);
            $models[] = $model;
        }
        return $models;
    }

    protected function clearRelation(Account $account, string $relation)
    {
        if (is_array($account->$relation)) {
            foreach ($account->$relation as $model) {
                $model->delete();
            }
        }
    }

    protected function clearRelationByRefClass(Account $account, string $manyToManyRelationClass)
    {
        $manyToManyRelationClass::deleteAll(['accountId' => $account->id]);
    }

    protected function fillAccountId(Account $account, array $models): array
    {
        foreach ($models as $model) {
            $model->accountId = $account->id;
        }
        return $models;
    }

    /**
     * @inheritdoc
     */
    public function accessRules()
    {
        return [
            [
                'allow' => true,
                'roles' => [Role::ROLE_SPECIALIST],
            ],
        ];
    }

    public function actionStep1()
    {
        $this->checkStep(1);

        [$dbTransaction, $data, $account] = $this->prepareData();
        $profile = $account->profile;
        $form = new RegistrationWizardStep1();

        $form->load($data, '');

        if (!$form->validate()) {
            return $form;
        }

        $data = $form->attributes;
        $account->setAttributes($data, false);
        $profile->setAttributes($data, false);

        if ($account->registrationStep <= 1) {
            $account->registrationStep = 2;
        }

        $isSuccess = $account->save(false) && $profile->save(false);

        if ($isSuccess) {
            $dbTransaction->commit();
        } else {
            $account->addError('account', 'Something went wrong');
            $dbTransaction->rollBack();
        }

        return new AccountResponse($account);
    }

    public function actionStep2()
    {
        $this->checkStep(2);
        [$dbTransaction, $data, $account] = $this->prepareData();
        $profile = $account->profile;

        $form = new RegistrationWizardStep2();
        $form->load($data, '');
        if (!$form->validate()) {
            return $form;
        }

        //load data
        $profile->setAttributes($form->attributes, false);

        $insuranceCompanies = $this->processMultipleModels(
            $form->attributes['insuranceCompanies'],
            AccountInsuranceCompany::class
        );
        $insuranceCompanies = $this->fillAccountId($account, $insuranceCompanies);

        $licenceStates = $this->processMultipleModels($form->attributes['licenceStates'], AccountLicenceState::class);
        $licenceStates = $this->fillAccountId($account, $licenceStates);

        $telehealthStates = $this->processMultipleModels(
            $form->attributes['telehealthStates'],
            AccountTelehealthState::class
        );
        $telehealthStates = $this->fillAccountId($account, $telehealthStates);

        if ($account->registrationStep <= 2) {
            $account->registrationStep = 3;
        }

        //save data
        $isSuccess = true;

        $this->clearRelation($account, 'licenceStates');
        foreach ($licenceStates as $model) {
            $isSuccess &= $model->save(false);
        }

        $this->clearRelation($account, 'accountInsurance');
        foreach ($insuranceCompanies as $model) {
            $isSuccess &= $model->save(false);
        }

        $this->clearRelation($account, 'telehealthStates');
        foreach ($telehealthStates as $model) {
            $isSuccess &= $model->save(false);
        }

        $isSuccess = $isSuccess && $account->save(false) && $profile->save(false);

        if ($isSuccess) {
            $dbTransaction->commit();
            $account->refresh();
        } else {
            $account->addError('account', 'Something went wrong');
            $dbTransaction->rollBack();
        }

        return new AccountResponse($account);
    }

    public function actionStep3()
    {
        $this->checkStep(3);
        [$dbTransaction, $data, $account] = $this->prepareData();

        $form = new RegistrationWizardStep3();
        $form->load($data, '');
        if (!$form->validate()) {
            return $form;
        }

        if ($account->registrationStep <= 3) {
            $account->registrationStep = 4;
        }

        //save data
        $isSuccess = true;

        //load data
        foreach (RegistrationWizardStep3::ATTR_RELATION_CLASSES as $attribute => $class) {
            $data[$attribute] = $this->processMultipleModels(
                $form->$attribute,
                RegistrationWizardStep3::ATTR_RELATION_CLASSES[$attribute]
            );
            $data[$attribute] = $this->fillAccountId($account, $data[$attribute]);
            $this->clearRelationByRefClass(
                $account,
                RegistrationWizardStep3::ATTR_RELATION_CLASSES[$attribute]
            );
            foreach ($data[$attribute] as $model) {
                $isSuccess &= $model->save(false);
            }
        }

        $isSuccess = $isSuccess && $account->save(false);

        if ($isSuccess) {
            $dbTransaction->commit();
            $account->refresh();
        } else {
            $account->addError('account', 'Something went wrong');
            $dbTransaction->rollBack();
        }

        return new AccountResponse($account);
    }

    public function actionStep4()
    {
        $this->checkStep(4);
        [$dbTransaction, $data, $account] = $this->prepareData();

        $form = new RegistrationWizardStep4();
        $form->load($data, '');
        if (!$form->validate()) {
            return $form;
        }

        //load data

        $educations = $this->processMultipleModels($form->attributes['educations'], AccountEducation::class);
        $educations = $this->fillAccountId($account, $educations);

        $certifications = $this->processMultipleModels($form->attributes['certifications'], AccountReward::class);
        $certifications = $this->fillAccountId($account, $certifications);

        if ($account->registrationStep <= 4) {
            $account->registrationStep = 5;
        }

        //save data
        $isSuccess = true;

        $this->clearRelation($account, 'educations');
        foreach ($educations as $model) {
            $isSuccess &= $model->save(false);
        }

        $this->clearRelation($account, 'certifications');
        foreach ($certifications as $model) {
            $isSuccess &= $model->save(false);
        }

        $isSuccess = $isSuccess && $account->save(false);

        if ($isSuccess) {
            $dbTransaction->commit();
            $account->refresh();
        } else {
            $account->addError('account', 'Something went wrong');
            $dbTransaction->rollBack();
        }

        return new AccountResponse($account);
    }

    public function actionStep5()
    {
        $this->checkStep(5);
        [$dbTransaction, $data, $account] = $this->prepareData();

        $form = new RegistrationWizardStep5();
        $form->load($data, '');
        if (!$form->validate()) {
            return $form;
        }

        //load data
        /**
         * @var AccountRate $rate
         */
        $rate = $account->rate ?? new AccountRate(['accountId' => $account->id]);
        $rate->setAttributes($form->attributes, false);
        if ($account->registrationStep <= 5) {
            $account->registrationStep = 6;
        }

        //save data
        $isSuccess = $rate->save(false) && $account->save(false);

        if ($isSuccess) {
            $dbTransaction->commit();
            $account->refresh();
        } else {
            $account->addError('account', 'Something went wrong');
            $dbTransaction->rollBack();
        }

        return new AccountResponse($account);
    }

    public function actionStep6UploadPhoto()
    {
        $this->checkStep(6);

        $account = Yii::$app->user->identity;

        $form = new RegistrationWizardStep6();
        $form->setScenario('upload');
        $form->file = UploadedFile::getInstance($form, 'file');
        if (!$form->validate()) {
            return $form;
        }

        $account->createThumbnails($form->file);
        return new AccountResponse($account);
    }

    public function actionStep6()
    {
        $this->checkStep(6);
        [$dbTransaction, $data, $account] = $this->prepareData();

        $form = new RegistrationWizardStep6();
        $form->load($data);
        if (!$form->validate()) {
            return $form;
        }

        $account->profile->setAttributes($form->attributes, false);

        $isSuccess = true;
        $data = $form->attributes();
        $data['languages'] = $this->processMultipleModels(
            $form->languages,
            AccountLanguage::class
        );

        $data['languages'] = $this->fillAccountId($account, $data['languages']);
        $this->clearRelationByRefClass(
            $account,
            AccountLanguage::class
        );

        foreach ($data['languages'] as $model) {
            $isSuccess &= $model->save(false);
        }

        if ($account->registrationStep <= 6) {
            $account->registrationStep = 7;
        }

        $isSuccess = $account->save(false) && $account->profile->save(false);

        if ($isSuccess) {
            $dbTransaction->commit();
        } else {
            $account->addError('account', 'Something went wrong');
            $dbTransaction->rollBack();
        }

        return new AccountResponse($account);
    }

    public function actionStep7()
    {
        $account = Yii::$app->user->identity;
        $account->status = AccountStatusHelper::STATUS_NEED_REVIEW;
        $account->save(false);

        QueueHelper::createTutorAgreementPdf($account->id, date('m/d/Y'));
    }
}

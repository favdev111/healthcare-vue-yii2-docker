<?php

namespace modules\account\controllers\api2;

use api2\components\RestController;
use common\components\UploadedFile;
use modules\account\models\Account;
use modules\account\models\api2\AccountInsuranceCompany;
use modules\account\models\api2\AccountLanguage;
use modules\account\models\api2\AccountLicenceState;
use modules\account\models\api2\AccountRate;
use modules\account\models\api2\AccountTelehealthState;
use modules\account\models\api2\forms\healthPros\ProfileSettingForm;
use modules\account\models\api2\forms\healthPros\ProfileUpdateForm;
use modules\account\models\api2\forms\healthPros\RoleUpdateForm;
use modules\account\models\api2\forms\healthPros\RatePolicyForm;
use modules\account\models\api2\forms\healthPros\SpecificationForm;
use modules\account\models\api2Patient\Profile;
use modules\account\models\Role;
use Yii;
use yii\db\ActiveRecordInterface;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class HealthProsController extends RestController
{
    public $profileModelClass = Profile::class;

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

    /**
     * @return array|ActiveRecordInterface
     * @throws NotFoundHttpException
     */
    protected function findModel()
    {
        /* @var $modelClass ActiveRecordInterface */
        $modelClass = $this->profileModelClass;
        $model = $modelClass::find()
            ->andWhere(['accountId' => Yii::$app->user->id])
            ->limit(1)
            ->one();

        if (isset($model)) {
            return $model;
        }

        throw new NotFoundHttpException("Object not found");
    }

    protected function getInitData(): array
    {
        return [Yii::$app->db->beginTransaction(), Yii::$app->request->post(), Yii::$app->user->identity];
    }


    public function actionSpecification()
    {
        [$dbTransaction, $data, $account] = $this->getInitData();

        $form = new SpecificationForm();

        $form->load($data, '');

        if (! $form->validate()) {
            return $form;
        }

        $isSuccess = true;

        foreach (SpecificationForm::ATTR_RELATION_CLASSES as $attribute => $class) {
            $data[$attribute] = $this->applyMultipleModels(
                $form->$attribute,
                SpecificationForm::ATTR_RELATION_CLASSES[$attribute]
            );
            $data[$attribute] = $this->populateAccountId($account, $data[$attribute]);
            $this->deleteRelationByRefClass(
                $account,
                SpecificationForm::ATTR_RELATION_CLASSES[$attribute]
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

        return '';
    }

    public function actionRole()
    {
        [$dbTransaction, $data, $account] = $this->getInitData();

        $profile = $account->profile;

        $form = new RoleUpdateForm();

        $form->load($data, '');
        if (! $form->validate()) {
            return $form;
        }

        $profile->setAttributes($form->attributes, false);

        $insuranceCompanies = $this->applyMultipleModels(
            $form->attributes['insuranceCompanies'],
            AccountInsuranceCompany::class
        );
        $insuranceCompanies = $this->populateAccountId($account, $insuranceCompanies);

        $licenceStates = $this->applyMultipleModels($form->attributes['licenceStates'], AccountLicenceState::class);
        $licenceStates = $this->populateAccountId($account, $licenceStates);

        $telehealthStates = $this->applyMultipleModels(
            $form->attributes['telehealthStates'],
            AccountTelehealthState::class
        );
        $telehealthStates = $this->populateAccountId($account, $telehealthStates);

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

        return '';
    }

    protected function populateAccountId(Account $account, array $models): array
    {
        foreach ($models as $model) {
            $model->accountId = $account->id;
        }

        return $models;
    }

    protected function applyMultipleModels(?array $data, string $className): array
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

    protected function deleteRelationByRefClass(Account $account, string $manyToManyRelationClass)
    {
        $manyToManyRelationClass::deleteAll(['accountId' => $account->id]);
    }

    protected function clearRelation(Account $account, string $relation)
    {
        if (is_array($account->$relation)) {
            foreach ($account->$relation as $model) {
                $model->delete();
            }
        }
    }

    public function actionProfile()
    {
        $model = $this->findModel();

        $form = new ProfileUpdateForm(['model' => $model]);

        $form->load(Yii::$app->getRequest()->post(), '');

        if (! $form->validate()) {
            return $form;
        }

        $model->setAttributes(
            array_filter($form->getAttributes()),
            false
        );

        if (! $model->save(false)) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;
    }

    public function actionRateAndPolicy()
    {
        [$dbTransaction, $data, $account] = $this->getInitData();

        $form = new RatePolicyForm();

        $form->load($data, '');
        if (! $form->validate()) {
            return $form;
        }

        $rate = $account->rate ?? new AccountRate(['accountId' => $account->id]);

        $rate->setAttributes($form->attributes, false);

        $isSuccess = $rate->save(false) && $account->save(false);

        if ($isSuccess) {
            $dbTransaction->commit();
            $account->refresh();
        } else {
            $account->addError('account', 'Something went wrong');
            $dbTransaction->rollBack();
        }

        return '';
    }

    public function actionProfileSetting()
    {
        [$dbTransaction, $data, $account] = $this->getInitData();

        $form = new ProfileSettingForm();

        $form->load($data);

        if (! $form->validate()) {
            return $form;
        }

        $account->profile->setAttributes($form->attributes, false);

        $isSuccess = true;
        $data = $form->attributes();
        $data['languages'] = $this->applyMultipleModels(
            $form->languages,
            AccountLanguage::class
        );

        $data['languages'] = $this->populateAccountId($account, $data['languages']);
        $this->deleteRelationByRefClass(
            $account,
            AccountLanguage::class
        );

        foreach ($data['languages'] as $model) {
            $isSuccess &= $model->save(false);
        }

        $isSuccess = $account->save(false) && $account->profile->save(false);

        if ($isSuccess) {
            $dbTransaction->commit();
        } else {
            $account->addError('account', 'Something went wrong');
            $dbTransaction->rollBack();
        }

        return "";
    }

    public function actionAvatar()
    {
        $account = Yii::$app->user->identity;

        $form = new ProfileSettingForm();
        $form->setScenario('upload');
        $form->file = UploadedFile::getInstance($form, 'file');
        if (! $form->validate()) {
            return $form;
        }

        $account->createThumbnails($form->file);

        return '';
    }
}

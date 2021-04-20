<?php

namespace modules\account\controllers\api2Patient;

use api2\components\RestController;
use modules\account\models\api2Patient\entities\healthProfile\insurance\HealthProfileInsurance;
use modules\account\models\api2Patient\forms\healthProfile\insurance\CreateInsuranceForm;
use common\helpers\Role;
use modules\account\models\api2Patient\forms\healthProfile\insurance\UpdateInsuranceForm;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * Class HealthProfileInsuranceController
 * @package modules\account\controllers\api2Patient
 */
class HealthProfileInsuranceController extends RestController
{
    /**
     * @inheritdoc
     */
    public function accessRules()
    {
        return [
            [
                'allow' => true,
                'roles' => [Role::ROLE_PATIENT],
            ],
        ];
    }

    /**
     * @return array|\common\models\healthProfile\insurance\HealthProfileInsurance|HealthProfileInsurance|null
     */
    public function actionIndex()
    {
        return HealthProfileInsurance::find()
            ->andWhere(['healthProfileId' => $this->currentAccount->mainHealthProfile->id])
            ->orderBy(['isPrimary' => SORT_DESC])
            ->all();
    }

    /**
     * @return \common\models\healthProfile\insurance\HealthProfileInsurance|CreateInsuranceForm|object|null
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $form = Yii::createObject(CreateInsuranceForm::class, [$this->currentAccount->mainHealthProfile]);
        $form->load($this->request->post());

        if (($insurance = $form->create()) !== null) {
            return $insurance;
        }

        return $form;
    }

    /**
     * @param $id
     * @return UpdateInsuranceForm|object
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $form = Yii::createObject(UpdateInsuranceForm::class, [
            $this->currentAccount->mainHealthProfile,
            $this->findModel($id),
        ]);
        $form->load($this->request->post());

        if (($insurance = $form->update()) !== null) {
            return $insurance;
        }

        return $form;
    }

    /**
     * @param $id
     * @return array|\common\models\healthProfile\insurance\HealthProfileInsurance|HealthProfileInsurance
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }

    /**
     * @param $id
     * @return array|\common\models\healthProfile\insurance\HealthProfileInsurance|HealthProfileInsurance
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = HealthProfileInsurance::find()
            ->andWhere(['id' => $id])
            ->andWhere(['healthProfileId' => $this->currentAccount->mainHealthProfile->id])
            ->one();

        if ($model) {
            return $model;
        }

        throw new NotFoundHttpException("Object not found: $id");
    }
}

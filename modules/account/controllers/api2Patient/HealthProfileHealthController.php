<?php

namespace modules\account\controllers\api2Patient;

use api2\components\RestController;
use modules\account\models\api2Patient\entities\healthProfile\HealthProfile;
use modules\account\models\api2Patient\forms\healthProfile\health\HealthForm;
use common\helpers\Role;
use Yii;
use yii\web\ForbiddenHttpException;

/**
 * Class HealthProfileHealthController
 * @package modules\account\controllers\api2Patient
 */
class HealthProfileHealthController extends RestController
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
     * @param $id
     * @return HealthProfile
     * @throws ForbiddenHttpException
     */
    private function getHealthProfile($id): HealthProfile
    {
        $model = $this->currentAccount
            ->getHealthProfiles()
            ->andWhere(['health_profile.id' => $id])
            ->one();

        if ($model) {
            return $model;
        }

        throw new ForbiddenHttpException("Invalid health profile");
    }

    /**
     * @param $healthProfileId
     * @return object|null
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate($healthProfileId)
    {
        $form = Yii::createObject(HealthForm::class, [$this->getHealthProfile($healthProfileId)]);
        $form->load($this->request->post());

        if (($healthProfile = $form->create()) !== null) {
            return $healthProfile->response;
        }

        return $form;
    }
}

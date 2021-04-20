<?php

namespace api2\controllers;

use api2\components\RestController;
use modules\account\models\api2Patient\entities\healthProfile\health\HealthDrink;
use modules\account\models\api2Patient\entities\healthProfile\health\HealthSmoke;

/**
 * Class SocialInfoController
 * @package api2\controllers
 */
class SocialInfoController extends RestController
{
    /**
     * @return array[]
     */
    public function accessRules()
    {
        return [
            [
                'allow' => true,
                'roles' => ['@'],
            ],
        ];
    }

    /**
     * @return HealthSmoke[]
     */
    public function actionSmoke()
    {
        return HealthSmoke::find()->all();
    }

    /**
     * @return HealthDrink[]
     */
    public function actionDrink()
    {
        return HealthDrink::find()->all();
    }
}

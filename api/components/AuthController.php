<?php

namespace api\components;

use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\helpers\ArrayHelper;

class AuthController extends ActiveController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'authenticator' => [
                    'class' => HttpBearerAuth::className(),
                    'except' => [
                        'xml'
                    ],
                ],
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => static::behaviorAccess(),
                    'except' => [
                        'xml'
                    ],
                ],
            ]
        );
    }

    /**
     * Access rules
     *
     * @return array
     */
    public function behaviorAccess()
    {
        return [];
    }
}

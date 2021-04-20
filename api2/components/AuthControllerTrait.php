<?php

namespace api2\components;

use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;

trait AuthControllerTrait
{
    /**
     * @inheritdoc
     */
    public function authBehaviors()
    {
        return [
            'authenticator' => [
                'class' => HttpBearerAuth::class,
                'except' => [
                    'options',
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => $this->accessRules(),
                'except' => [
                    'options',
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function accessRules()
    {
        return [];
    }
}

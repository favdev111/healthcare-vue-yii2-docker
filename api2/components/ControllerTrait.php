<?php

namespace api2\components;

use Yii;
use yii\filters\ContentNegotiator;
use yii\filters\RateLimiter;
use yii\filters\VerbFilter;
use yii\web\Response;

trait ControllerTrait
{
    public $enableDeviceParamsValidation = true;
    public $responseClass;

    public function otherBehaviors()
    {
        return [
            'verbFilter' => [
                'class' => VerbFilter::class,
                'actions' => $this->verbs(),
            ],
            'rateLimiter' => [
                'class' => RateLimiter::class,
            ],
        ];
    }

    public function contentNegoriatorBehaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    public function validateDeviceParams()
    {
        if (
            $this->enableDeviceParamsValidation
            && Yii::$app->getErrorHandler()->exception === null
        ) {
            Yii::$app->getRequest()->validateDeviceParams();
        }
    }

    protected function getPlatform()
    {
        return Yii::$app->request->getPlatform();
    }

    protected function getDeviceToken()
    {
        return Yii::$app->request->getDeviceToken();
    }

    protected function serializeData($data)
    {
        return (new $this->serializer(['responseClass' => $this->responseClass]))->serialize($data);
    }
}

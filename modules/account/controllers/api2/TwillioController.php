<?php

namespace modules\account\controllers\api2;

use modules\account\models\api2\search\SymptomSearch;
use modules\account\models\api2\Symptom;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;
use yii\rest\IndexAction;

class TwillioController extends \api2\components\RestController
{
    public $modelClass = Symptom::class;

    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'prepareDataProvider' => function (IndexAction $action) {
                    $searchModel = new SymptomSearch();
                    return $searchModel->search(\Yii::$app->getRequest()->getQueryParams());
                },
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
        ];
    }

    public function behaviors()
    {
        return [
            'pageCache' => [
                'class' => \yii\filters\PageCache::class,
                'duration' => \Yii::$app->params['cachePageDuration'],
                'only' => ['index'],
                'variations' => [
                    \Yii::$app->request->pathInfo,
                    \Yii::$app->request->queryParams,
                ],
            ],
        ];
    }

    public function actionRoomcreate()
    {
        $client_name = \Yii::$app->request->post('client_name');
        $sid = env('TWILLIO_SID');
        $key = env('TWILIO_API_KEY');
        $secreate = env('TWILIO_API_SECRET');
        $token = new AccessToken(
            $sid,
            $key,
            $secreate,
            3600,
            $client_name
        );
        $grant = new VideoGrant();
        $token->addGrant($grant);

        $response = [
            "client_name" => $client_name,
            "token" => $token->toJWT()
        ];
        return $response;
    }
}

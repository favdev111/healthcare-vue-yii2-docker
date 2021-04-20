<?php

namespace api2\controllers;

use api2\components\ActiveController;
use modules\account\models\api2\HealthGoal;
use modules\account\models\api2\search\HealthGoalSearch;
use yii\rest\IndexAction;

class HealthGoalController extends ActiveController
{
    public $modelClass = HealthGoal::class;
    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'prepareDataProvider' => function (IndexAction $action) {
                    $searchModel = new HealthGoalSearch();
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
                ],
            ],
        ];
    }
}

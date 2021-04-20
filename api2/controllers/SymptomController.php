<?php

namespace api2\controllers;

use api2\components\ActiveController;
use modules\account\models\api2\search\SymptomSearch;
use modules\account\models\api2\Symptom;
use yii\rest\IndexAction;

class SymptomController extends ActiveController
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
}

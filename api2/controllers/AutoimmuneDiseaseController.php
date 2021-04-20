<?php

namespace api2\controllers;

use api2\components\ActiveController;
use modules\account\models\api2\AutoimmuneDisease;
use modules\account\models\api2\search\AutoimmuneDiseaseSearch;
use yii\rest\IndexAction;

class AutoimmuneDiseaseController extends ActiveController
{
    public $modelClass = AutoimmuneDisease::class;

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'prepareDataProvider' => function (IndexAction $action) {
                    $searchModel = new AutoimmuneDiseaseSearch();
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

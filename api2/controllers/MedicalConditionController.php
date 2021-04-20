<?php

namespace api2\controllers;

use api2\components\ActiveController;
use modules\account\models\api2\MedicalCondition;
use modules\account\models\api2\search\MedicalConditionSearch;
use yii\rest\IndexAction;

class MedicalConditionController extends ActiveController
{
    public $modelClass = MedicalCondition::class;
    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'prepareDataProvider' => function (IndexAction $action) {
                    $searchModel = new MedicalConditionSearch();
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

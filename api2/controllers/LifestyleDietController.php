<?php

namespace api2\controllers;

use api2\components\ActiveController;
use modules\account\models\api2\health\LifestyleDiet;
use modules\account\models\api2\search\LifestyleDietSearch;
use Yii;
use yii\rest\IndexAction;

/**
 * Class LifestyleDietController
 * @package api2\controllers
 */
class LifestyleDietController extends ActiveController
{
    public $modelClass = LifestyleDiet::class;

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
                    $searchModel = Yii::createObject(LifestyleDietSearch::class);
                    return $searchModel->search($this->request->queryParams);
                },
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
        ];
    }
}

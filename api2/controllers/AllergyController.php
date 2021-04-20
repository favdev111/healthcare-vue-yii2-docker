<?php

namespace api2\controllers;

use api2\components\ActiveController;
use modules\account\models\api2\health\allergy\Allergy;
use modules\account\models\api2\search\allergy\AllergySearch;
use Yii;
use yii\rest\IndexAction;

/**
 * Class AllergyController
 * @package api2\controllers
 */
class AllergyController extends ActiveController
{
    public $modelClass = Allergy::class;

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
                    $searchModel = Yii::createObject(AllergySearch::class);
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

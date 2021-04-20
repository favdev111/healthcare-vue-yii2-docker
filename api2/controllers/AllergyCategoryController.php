<?php

namespace api2\controllers;

use api2\components\ActiveController;
use modules\account\models\api2\health\allergy\AllergyCategory;
use modules\account\models\api2\search\allergy\AllergyCategorySearch;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\IndexAction;

/**
 * Class AllergyCategoryController
 * @package api2\controllers
 */
class AllergyCategoryController extends ActiveController
{
    public $modelClass = AllergyCategory::class;

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
                    $searchModel = Yii::createObject(AllergyCategorySearch::class);
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

    /**
     * Special method for health tab
     */
    public function actionGetAll()
    {
        $allergyCategories = AllergyCategory::find()->with('allergies', 'medicalAllergyGroup')->all();
        $data = [];
        /** @var AllergyCategory $allergyCategory */
        foreach ($allergyCategories as $allergyCategory) {
            $medicalAllergyGroup = (bool)$allergyCategory->medicalAllergyGroup;

            if ($medicalAllergyGroup) {
                $items = [
                    [
                        'id' => $allergyCategory->id,
                        'name' => $allergyCategory->name,
                    ]
                ];
            } else {
                $items = ArrayHelper::toArray($allergyCategory->allergies, [
                    AllergyCategory::class => [
                        'id',
                        'name'
                    ],
                ]);
            }

            $data[] = [
                'category' => [
                    'id' => $allergyCategory->id,
                    'title' => $allergyCategory->name,
                    'hasItems' => !$medicalAllergyGroup,
                ],
                'items' => $items
            ];
        }

        return $data;
    }
}

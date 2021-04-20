<?php

namespace modules\account\controllers\api;

use api\components\rbac\Rbac;
use modules\account\models\api\search\ZipCodeSearch;
use Yii;
use yii\rest\IndexAction;

class LocationController extends \api\components\AuthController
{
    public $modelClass = 'modules\account\models\api\ZipCode';

    /**
     * @inheritdoc
     */
    public function behaviorAccess()
    {
        return [
            [
                'allow' => true,
                'roles' => [Rbac::PERMISSION_BASE_B2B_PERMISSIONS],
            ],
        ];
    }

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = function (IndexAction $action) {
            $searchModel = new ZipCodeSearch();
            return $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        };
        unset($actions['update']);
        unset($actions['create']);
        unset($actions['view']);
        return $actions;
    }

    /**
     * @OA\Get(
     *     path="/location/",
     *     tags={"location"},
     *     summary="List of locations",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="zip code",
     *         in="query",
     *         name="code",
     *         required=false,
     *         type="integer"
     *     ),
     *       @OA\Parameter(
     *         description="Page number",
     *         in="query",
     *         name="page",
     *         required=false,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="Post payment per page",
     *         in="query",
     *         name="per-page",
     *         required=false,
     *         type="integer"
     *     ),
     *      @OA\Parameter(
     *         description="To return extra data, for example: city",
     *         in="query",
     *         name="expand",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),
     *
     *     @OA\Response(response="200", description="")
     * )
     */
}

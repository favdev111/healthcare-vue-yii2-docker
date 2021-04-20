<?php

namespace modules\account\controllers\api;

use api\components\rbac\Rbac;
use modules\account\models\Role;
use modules\account\models\search\TeamSearch;
use modules\account\models\Team;
use Yii;
use yii\rest\IndexAction;

class TeamController extends \api\components\AuthController
{
    public $modelClass = Team::class;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => 'yii\filters\PageCache',
                'only' => ['index'],
                'duration' => 60 * 60 * 3,
                'variations' => [
                    Yii::$app->request->get(),
                ],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function behaviorAccess()
    {
        return [
            [
                'allow' => true,
                //company admin and company owner (owner rights includes admin rights)
                'roles' => [Rbac::PERMISSION_BASE_B2B_PERMISSIONS],
            ],
        ];
    }

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = function (IndexAction $action) {
            $searchModel = new TeamSearch();
            return $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        };

        unset(
            $actions['create'],
            $actions['update'],
            $actions['delete']
        );

        return $actions;
    }

    /**
     * @OA\Get(
     *     path="/teams/",
     *     tags={"teams"},
     *     summary="List of teams",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Page number",
     *         in="query",
     *         name="page",
     *         required=false,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="Per page",
     *         in="query",
     *         name="per-page",
     *         required=false,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="Query string",
     *         in="query",
     *         name="query",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="Sort",
     *         in="query",
     *         name="sort",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Response(response="200", description="")
     * )
     */
}

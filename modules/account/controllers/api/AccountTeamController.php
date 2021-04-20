<?php

namespace modules\account\controllers\api;

use modules\account\models\api\AccountTeam;
use modules\account\models\api\search\AccountTeamSearch;
use modules\account\models\Role;
use modules\account\models\search\TeamSearch;
use modules\account\models\Team;
use Yii;
use yii\rest\IndexAction;

class AccountTeamController extends \api\components\AuthController
{
    public $modelClass = AccountTeam::class;
    /**
     * @inheritdoc
     */
    public function behaviorAccess()
    {
        return [
            [
                'allow' => true,
                'roles' => [Role::ROLE_CRM_ADMIN],
            ],
        ];
    }

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = function (IndexAction $action) {
            $searchModel = new AccountTeamSearch();
            return $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        };
        return $actions;
    }

    /**
     * @OA\Get(
     *     path="/account-team/",
     *     tags={"account-team"},
     *     summary="List of account-team",
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
     *         description="accountId",
     *         in="query",
     *         name="accountId",
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

    /**
     * @OA\Post(
     *     path="/account-team/",
     *     tags={"account-team"},
     *     summary="Add account to team",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="accountId",
     *                 type="integer",
     *                 description="Employee ID"
     *             ),
     *             @OA\Property(
     *                 property="teamId",
     *                 type="integer",
     *                 description="Team ID"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="201", description="created"),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */

    /**
     * @OA\Put(
     *     path="/account-team/{id}/",
     *     tags={"account-team"},
     *     summary="Add account to team",
     *     description="",
     *     security={{"Bearer":{}}},
     *    @OA\Parameter(
     *         description="id of relation",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="accountId",
     *                 type="integer",
     *                 description="Employee ID"
     *             ),
     *             @OA\Property(
     *                 property="teamId",
     *                 type="integer",
     *                 description="Team ID"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
}

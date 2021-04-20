<?php

namespace modules\account\controllers\api;

use api\components\rbac\Rbac;
use common\helpers\Role;
use common\models\PostPaymentSearch;
use Yii;
use yii\rest\IndexAction;

class PostPaymentController extends \api\components\AuthController
{
    public $modelClass = 'modules\account\models\api\PostPayment';

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
            $searchModel = new PostPaymentSearch();
            return $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        };

        return $actions;
    }

    /**
     * @OA\Get(
     *     path="/post-payment/",
     *     tags={"post-payment"},
     *     summary="List of post payment",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Client account ID",
     *         in="query",
     *         name="accountId",
     *         required=false,
     *         type="integer"
     *     ),
     *      @OA\Parameter(
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
     *         description="To return extra data, for example, isAllowedReChargeTransaction, transaction",
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

    /**
     * @OA\Get(
     *     path="/post-payment/{id}/",
     *     tags={"post-payment"},
     *     summary="Get post-payment data",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="post-payment account ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *      @OA\Parameter(
     *         description="To return extra data, for example, isAllowedReChargeTransaction, transaction",
     *         in="query",
     *         name="expand",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),
     *
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="404", description="")
     * )
     */


    /**
     * @OA\Delete(
     *     path="/post-payment/{id}/",
     *     tags={"post-payment"},
     *     summary="Remove post-payment",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="post-payment ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Response(response="204", description=""),
     *     @OA\Response(response="404", description="")
     * )
     */

    /**
     * @OA\Post(
     *     path="/post-payment/",
     *     tags={"post-payment"},
     *     summary="Add new post-payment",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="accountId",
     *                 type="integer",
     *                 description="Id account"
     *             ),
     *     @OA\Property(
     *                 property="amount",
     *                 type="string",
     *                 description="Amount"
     *             ),
     *     @OA\Property(
     *                 property="date",
     *                 type="string",
     *                 description="date in format YYYY-MM-DD"
     *             ),
     *     @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 description="Status"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */

    /**
     * @OA\Put(
     *     path="/post-payment/{id}/",
     *     tags={"post-payment"},
     *     summary="Update post-payment",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="post-payment ID",
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
     *                 description="Id account"
     *             ),
     *     @OA\Property(
     *                 property="amount",
     *                 type="string",
     *                 description="Amount"
     *             ),
     *     @OA\Property(
     *                 property="date",
     *                 type="string",
     *                 description="date in format YYYY-MM-DD"
     *             ),
     *     @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 description="status"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
}

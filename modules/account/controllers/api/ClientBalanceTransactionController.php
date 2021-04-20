<?php

namespace modules\account\controllers\api;

use api\components\rbac\Rbac;
use common\helpers\Role;
use common\models\ClientBalancePdf;
use kartik\mpdf\Pdf;
use modules\account\models\api\AccountClient;
use modules\account\models\api\search\ClientBalanceTransactionSearch;
use modules\account\models\ClientBalanceTransaction;
use Yii;
use yii\rest\IndexAction;

/**
 * Default controller for ClientBalanceTransaction model
 */
class ClientBalanceTransactionController extends \api\components\AuthController
{
    /**
     * @inheritdoc
     */
    public $modelClass = 'modules\account\models\api\ClientBalanceTransaction';

    /**
     * @inheritdoc
     */
    public function behaviorAccess()
    {
        return [
            [
                'allow' => true,
                'actions' => ['create', 'update', 'delete'],
                'roles' => [Rbac::PERMISSION_CAN_CHANGE_BALANCE],
            ],
            [
                'allow' => true,
                'roles' => [Rbac::PERMISSION_BASE_B2B_PERMISSIONS],
                'actions' => [
                    'index',
                    'view',
                    'pdf',
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = function (IndexAction $action) {
            $searchModel = new ClientBalanceTransactionSearch();
            return $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        };

        return $actions;
    }

    /**
     * @OA\Get(
     *     path="/client-balance/",
     *     tags={"client-balance"},
     *     summary="List of client balance changes",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Page number",
     *         in="query",
     *         name="page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Client balance transactions per page",
     *         in="query",
     *         name="per-page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Query string",
     *         in="query",
     *         name="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Filter client balance transactions by client",
     *         in="query",
     *         name="clientId",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Filter client balance transactions by createdAt",
     *         in="query",
     *         name="dateFrom",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Filter client balance transactions by createdAt",
     *         in="query",
     *         name="dateTo",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="To return client balance transaction extra data, for example, you can add client to return client account data or transaction to get related transaction (transaction,lesson, refundButton, sumRefund, clientBalanceAmount)",
     *         in="query",
     *         name="expand",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),
     *     @OA\Response(response="200", description="")
     * )
     */

    /**
     * @OA\Get(
     *     path="/client-balance/{id}/",
     *     tags={"client-balance"},
     *     summary="Get client balance transaction data",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Client Balance Transaction ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="To return client balance transaction extra data, for example, you can add client to return client account data or transaction to get related transaction (transaction,lesson, refundButton, sumRefund, clientBalanceAmount)",
     *         in="query",
     *         name="expand",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="404", description="")
     * )
     */

    /**
     * @OA\Post(
     *     path="/client-balance/",
     *     tags={"client-balance"},
     *     summary="Add new manual client balance transaction",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="clientId",
     *                 type="integer",
     *                 description="Client ID"
     *             ),
     *             @OA\Property(
     *                 property="amount",
     *                 type="integer",
     *                 description="Transaction amount (can be negative)"
     *             ),
     *            @OA\Property(
     *                 property="note",
     *                 type="string",
     *                 description="Additional note"
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Client balance transaction created"),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */

    /**
     * @OA\Get(
     *     path="/client-balance/{clientId}/pdf/",
     *     tags={"client-balance"},
     *     summary="Get client transaction pdf",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Client account ID",
     *         in="path",
     *         name="clientId",
     *         required=false,
     *         type="integer"
     *     ),
     *      @OA\Parameter(
     *         description="From date filter in form m/d/Y",
     *         in="query",
     *         name="dateFrom",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="To date filter in form m/d/Y",
     *         in="query",
     *         name="dateTo",
     *         required=false,
     *         type="string"
     *     ),
     *
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="404", description="")
     * )
     */
    public function actionPdf($clientId, $dateFrom = null, $dateTo = null)
    {
        $pdf = new ClientBalancePdf(['clientId' => $clientId]);
        $pdf->title = 'Client balance transactions';
        $pdf->fileName = 'ClientBalanceTransactions.pdf';
        return $pdf->getPdfAsResponse();
    }
}

<?php

namespace modules\payment\controllers\api;

use api\components\rbac\Rbac;
use common\helpers\Role;
use common\models\RefundData;
use kartik\mpdf\Pdf;
use modules\account\models\api\AccountClient;
use modules\account\models\api\PartialRefund;
use modules\payment\models\api\search\TransactionSearch;
use modules\payment\models\api\Transaction;
use Yii;
use yii\rest\IndexAction;

/**
 * Default controller for Transaction model
 */
class TransactionController extends \api\components\AuthController
{
    /**
     * @inheritdoc
     */
    public $modelClass = 'modules\payment\models\api\Transaction';

    /**
     * @inheritdoc
     */
    public function behaviorAccess()
    {
        return [
            [
                'allow' => true,
                'actions' => ['re-charge', 'refund', 'create', 'update', 'delete'],
                'roles' => [Rbac::PERMISSION_CAN_CHANGE_BALANCE],
            ],
            [
                'allow' => true,
                'roles' => [Rbac::PERMISSION_BASE_B2B_PERMISSIONS],
                'actions' => [
                    'index',
                    'view',
                    'transaction-pdf'
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = function (IndexAction $action) {
            $searchModel = new TransactionSearch();
            return $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        };

        return $actions;
    }

    /**
     * @OA\Get(
     *     path="/transactions/{clientId}/transaction-pdf/",
     *     tags={"transactions"},
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
    public function actionTransactionPdf($clientId, $dateFrom = null, $dateTo = null)
    {
        //temporarily disabled
        Yii::$app->response->setStatusCode(404);
        return;
        $client = AccountClient::find()->where(['id' => $clientId])->limit(1)->one();
        if (empty($client)) {
            Yii::$app->response->statusCode = 422;
            return [
                ['field' => 'clientId', 'message' => 'Client not found']
            ];
        }

        $transactionSearch = (new TransactionSearch())->selectOnlySuccess(true);
        $transactionSearch->studentId = $clientId;
        $transaction = $transactionSearch->search(Yii::$app->request->get())->getModels();


        $content =  $this->renderPartial('transactionTable', ['client' => $client, 'transactions' => $transaction]);

        $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_STRING,
            'content' => $content,
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            'cssInline' => '.kv-heading-1{font-size:18px}',
            'options' => ['title' => 'Transactions'],
        ]);

        return Yii::$app->response->sendContentAsFile(
            $pdf->render(),
            'transactions.pdf',
            [
                'mimeType' => 'application/pdf',
                'inline' => true,
            ]
        );
    }


    /**
     * @OA\Get(
     *     path="/transactions/",
     *     tags={"transactions"},
     *     summary="List of transactions",
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
     *         description="Transactions per page",
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
     *         description="Filter transactions by client",
     *         in="query",
     *         name="studentId",
     *         required=false,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="To return transaction extra data (client, isPartialRefundOfGroupTransaction)",
     *         in="query",
     *         name="expand",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),@OA\Parameter(
     *         description="Filter  transactions by createdAt",
     *         in="query",
     *         name="dateFrom",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="Filter  transactions by createdAt",
     *         in="query",
     *         name="dateTo",
     *         required=false,
     *         type="string"
     *     ),
     *      @OA\Parameter(
     *         description="Filter without failed",
     *         in="query",
     *         name="withoutFailed",
     *         required=false,
     *         type="integer"
     *     ),

     *     @OA\Response(response="200", description="")
     * )
     */

    /**
     * @OA\Get(
     *     path="/transactions/{id}/",
     *     tags={"transactions"},
     *     summary="Get transactions data",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="transaction ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="To return transaction extra data (client, isPartialRefundOfGroupTransaction)" ,
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
     * @OA\Post(path="/transactions/re-charge/",
     *    tags={"transactions"},
     *    summary="re charge transactions",
     *    description="",
     *    security={{"Bearer":{}}},
     *    @OA\RequestBody(
     *        @OA\Schema(
     *            type="object",
     *            @OA\Property(
     *                property="id",
     *                type="integer",
     *                description="Id transaction"
     *            )
     *        )
     *    ),
     *    @OA\Response(response="200", description="")
     * )
     */
    public function actionReCharge()
    {
        $id = Yii::$app->request->post('id');
        if (empty($id)) {
            Yii::$app->response->statusCode = 422;
            return [
                [
                    'field' => 'id',
                    'message' => 'Id is empty',
                ],
            ];
        }

        $transaction = Transaction::findOne($id);
        if (empty($transaction)) {
            Yii::$app->response->statusCode = 404;
            return [
                ['field' => 'Transaction', 'message' => 'Transaction not found.']
            ];
        } else if ($newTransaction = Transaction::reCharge($transaction)) {
            return $newTransaction;
        } else {
            Yii::$app->response->statusCode = 422;
            return [
                ['field' => 'id', 'message' => 'This transaction can not be processed.']
            ];
        }
    }

    /**
     * @OA\Post(path="/transactions/refund/{id}/",
     *    tags={"transactions"},
     *    summary="Refund transactions",
     *    description="",
     *    security={{"Bearer":{}}},
     *      @OA\Parameter(
     *         description="Transaction ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *    @OA\RequestBody(
     *        @OA\Schema(
     *            type="object",
     *            @OA\Property(
     *                property="amount",
     *                type="number",
     *                description="Refund amount"
     *            )
     *        )
     *    ),
     *    @OA\Response(response="200", description="")
     * )
     */
    public function actionRefund($id)
    {
        $refundData = new RefundData();
        $refundData->transactionId = $id;
        $refundData->amount = Yii::$app->request->post('amount');

        $refundData->refund();

        $refundData->amount = Transaction::amountToDollars($refundData->amount);
        return $refundData;
    }
}

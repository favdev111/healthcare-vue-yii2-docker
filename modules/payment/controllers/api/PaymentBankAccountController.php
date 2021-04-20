<?php

namespace modules\payment\controllers\api;

use common\helpers\Role;
use modules\payment\models\api\AddCompanyBAForm;
use modules\payment\models\api\search\PaymentBankAccountSearch;
use modules\payment\models\api\PaymentBankAccount;
use Yii;
use yii\rest\IndexAction;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * Default controller for Payment Bank Account model
 */
class PaymentBankAccountController extends \api\components\AuthController
{
    /**
     * @inheritdoc
     */
    public $modelClass = 'modules\payment\models\api\PaymentBankAccount';

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

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = function (IndexAction $action) {
            $searchModel = new PaymentBankAccountSearch();
            return $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        };
        unset($actions['create']);
        return $actions;
    }


    /**
     * @OA\Post(
     *     path="/payment-bank-accounts/",
     *     tags={"payment-bank-accounts"},
     *     summary="Add new payment bank account for own account (Used for Company)",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="bankToken",
     *                 type="string",
     *                 description="Bank Token"
     *             ),
     *              @OA\Property(
     *                 property="companyId",
     *                 type="integer",
     *                 description="Id of company"
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response="201", description="Bank account created"),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
    public function actionCreate()
    {
        $form = new AddCompanyBAForm();
        $form->load(Yii::$app->request->post(), '');
        $bankAccount = $form->save();
        if ($form->hasErrors()) {
            return $form;
        }
        if (!$bankAccount) {
            throw new HttpException(500, 'Failed to add Bank Account');
        }
        return $bankAccount;
    }


    /**
     * @OA\Post(
     *     path="/payment-bank-accounts/verify/{id}/",
     *     tags={"payment-bank-accounts"},
     *     summary="Verify ACH Payment Bank Account (Used for Company)",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="payment bank account ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="deposit1",
     *                 type="string",
     *                 description="Deposit 1"
     *             ),
     *             @OA\Property(
     *                 property="deposit2",
     *                 type="string",
     *                 description="Deposit 2"
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response="201", description="Bank account created"),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */

    /**
     * @param $id integer
     * @return array|null|\yii\db\ActiveRecord
     * @throws NotFoundHttpException
     */
    public function actionVerify($id)
    {
        /**
         * @var $paymentBankAccount PaymentBankAccount
         */
        $paymentBankAccount = PaymentBankAccount::find()
            ->andWhere(['id' => $id])
            ->andWhere(['verified' => false])
            ->limit(1)
            ->one();
        if (!$paymentBankAccount) {
            throw new NotFoundHttpException();
        }

        $paymentBankAccount->verify(Yii::$app->request->post());
        return $paymentBankAccount;
    }

    /**
     * @OA\Get(
     *     path="/payment-bank-accounts/",
     *     tags={"payment-bank-accounts"},
     *     summary="List of payment bank accounts",
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
     *         description="Bank accounts per page",
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
     *         description="To return bank account extra data",
     *         in="query",
     *         name="expand",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),
     *     @OA\Parameter(
     *         description="Company id",
     *         in="query",
     *         name="companyId",
     *         required=false,
     *         type="integer",
     *     ),
     *     @OA\Response(response="200", description="")
     * )
     */

    /**
     * @OA\Get(
     *     path="/payment-bank-accounts/{id}/",
     *     tags={"payment-bank-accounts"},
     *     summary="Get bank account data",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="bank account ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="To return bank account extra data",
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
     * @OA\Delete(
     *     path="/payment-bank-accounts/{id}/",
     *     tags={"payment-bank-accounts"},
     *     summary="Delete Payment Bank Account",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Payment Bank Account ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Response(response="204", description=""),
     *     @OA\Response(response="404", description="")
     * )
     */

    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action === 'delete') {
            /**
             * @var $model PaymentBankAccount
             */
            if ($model->isActive()) {
                throw new HttpException(403, 'Active bank account can not be removed.');
            }
        }
    }
}

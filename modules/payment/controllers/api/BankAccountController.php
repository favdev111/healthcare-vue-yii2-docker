<?php

namespace modules\payment\controllers\api;

use common\helpers\Role;
use modules\payment\models\api\PaymentInfo;
use modules\payment\models\api\search\BankAccountSearch;
use Yii;
use yii\rest\IndexAction;

/**
 * Default controller for BankAccount model
 */
class BankAccountController extends \api\components\AuthController
{
    /**
     * @inheritdoc
     */
    public $modelClass = 'modules\payment\models\api\BankAccount';

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
            $searchModel = new BankAccountSearch();
            return $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        };

        unset($actions['create']);

        return $actions;
    }

    /**
     * @OA\Get(
     *     path="/bank-accounts/",
     *     tags={"bank-accounts"},
     *     summary="List of bank accounts",
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
     *         description="Bank accounts per page",
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
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="Company account id",
     *         in="query",
     *         name="companyId",
     *         required=false,
     *         type="integer",
     *     ),
     *     @OA\Parameter(
     *         description="To return bank account extra data, for example, you can add account to return account data",
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
     *     path="/bank-accounts/{id}/",
     *     tags={"bank-accounts"},
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
     *         description="To return bank account extra data, for example, you can add account to return account data",
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
     *     path="/bank-accounts/{id}/",
     *     tags={"bank-accounts"},
     *     summary="Delete Bank account",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Bank account ID",
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
     * @OA\Put(
     *     path="/bank-accounts/{id}/",
     *     tags={"bank-accounts"},
     *     summary="Update bank account",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Bank account ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="active",
     *                 type="boolean",
     *                 description="Set Bank Account as active"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="201", description="Bank account updated"),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */

    /**
     * @OA\Post(
     *     path="/bank-accounts/",
     *     tags={"bank-accounts"},
     *     summary="Add new bank account",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="ssn",
     *                 type="string",
     *                 description="Last 4 digits of SSN"
     *             ),
     *             @OA\Property(
     *                 property="bankToken",
     *                 type="string",
     *                 description="Stripe Bank Account Token"
     *             ),
     *             @OA\Property(
     *                 property="city",
     *                 type="string",
     *                 description="Bank Account Owner City"
     *             ),
     *             @OA\Property(
     *                 property="state",
     *                 type="string",
     *                 description="Bank Account Owner State"
     *             ),
     *             @OA\Property(
     *                 property="zipCode",
     *                 type="string",
     *                 description="Bank Account Owner Zip Code"
     *             ),
     *             @OA\Property(
     *                 property="addressLine1",
     *                 type="string",
     *                 description="Bank Account Owner Address Line 1"
     *             ),
     *             @OA\Property(
     *                 property="piiToken",
     *                 type="string",
     *                 description="Stripe Token for Full SSN. Should be sent separately from all other fields."
     *             ),
     *             @OA\Property(
     *                 property="document",
     *                 type="string",
     *                 description="Document file in png, jpg formats. Sent via multipart/form-data. Should be sent separately from all other fields."
     *             ),
     *              @OA\Property(
     *                 property="companyId",
     *                 type="integer",
     *                 description="Company account Id."
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Bank account created"),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
    public function actionCreate()
    {
        $paymentInfo = new PaymentInfo();

        $paymentInfo->load(Yii::$app->request->post(), '');
        if ($paymentInfo->save()) {
            return $paymentInfo->bankAccount ? $paymentInfo->bankAccount : $paymentInfo->paymentAccount;
        }
        return $paymentInfo;
    }
}

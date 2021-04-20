<?php

namespace modules\payment\controllers\api;

use common\helpers\Role;
use modules\payment\models\api\CompanyPaymentCustomer;
use modules\payment\models\api\PaymentCustomer;
use modules\payment\models\api\search\CardInfoSearch;
use Yii;
use yii\rest\IndexAction;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Default controller for Payment Account model
 */
class PaymentAccountController extends \api\components\AuthController
{
    /**
     * @inheritdoc
     */
    public $modelClass = 'modules\payment\models\api\PaymentAccount';

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
     * @OA\Get(
     *     path="/payment-accounts/{id}/",
     *     tags={"payment-accounts"},
     *     summary="Get payment account",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Payment Account ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="To return payment account extra data, for example, you can add bankAccounts to return bank accounts list",
     *         in="query",
     *         name="expand",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(
     *             property="accountId",
     *             type="integer",
     *             description="Client ID"
     *         ),
     *         @OA\Property(
     *             property="billingAddressVerified",
     *             type="boolean",
     *             description="Whether Billing Address and last 4 digits of SSN should be provided during bank account creation."
     *         ),
     *         @OA\Property(
     *             property="extraDataFields",
     *             type="array",
     *             description="List of extra fields required for Stripe verification. e.g. full ssn, document"
     *         ),
     *     ),
     *     @OA\Response(response="200", description="")
     * )
     */
}

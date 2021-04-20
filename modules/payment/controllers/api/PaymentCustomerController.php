<?php

namespace modules\payment\controllers\api;

use api\components\rbac\Rbac;
use common\helpers\Role;
use modules\payment\models\api\CompanyPaymentCustomer;
use modules\payment\models\api\PaymentCustomer;
use modules\payment\models\api\search\CardInfoSearch;
use Yii;
use yii\db\ActiveRecordInterface;
use yii\rest\IndexAction;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Default controller for Payment Customer model
 */
class PaymentCustomerController extends \api\components\AuthController
{
    /**
     * @inheritdoc
     */
    public $modelClass = 'modules\payment\models\api\PaymentCustomer';

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

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        $actions['view']['findModel'] = [$this, 'findModel'];
        unset($actions['update']);
        return $actions;
    }

    public function findModel($id)
    {
        $model = PaymentCustomer::findOne($id);
        if (!$model) {
            $model = CompanyPaymentCustomer::findOne($id);
        }

        if (isset($model)) {
            return $model;
        }

        throw new NotFoundHttpException("Object not found: $id");
    }


    public function actionUpdate($id)
    {
        $model = PaymentCustomer::findOne($id);
        if (!$model) {
            $model = CompanyPaymentCustomer::findOne($id);
        }

        if (!$model) {
            throw new NotFoundHttpException();
        }

        $this->checkAccess($this->action->id, $model);

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save() === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }
        return $model;
    }

    /**
     * @OA\Put(
     *     path="/payment-customers/{id}/",
     *     tags={"payment-customers"},
     *     summary="Update payment customer",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Payment Customer ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="newActiveCardId",
     *                 type="integer",
     *                 description="Card ID to use as Active for this customer"
     *             ),
     *             @OA\Property(
     *                 property="newActiveBankAccountId",
     *                 type="integer",
     *                 description="Bank Account ID to use as Active for this customer"
     *             ),
     *             @OA\Property(
     *                 property="autorenew",
     *                 type="integer",
     *                 enum={"1", "0"},
     *                 description="Autorenew flag for client payment account"
     *             ),
     *             @OA\Property(
     *                 property="packagePrice",
     *                 type="integer",
     *                 description="Autorenew package price. Null when no package selected."
     *             ), @OA\Property(
     *                 property="triggerPackageCharge",
     *                 type="integer",
     *                 description="Set it to 1 to charge client."
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */

    /**
     * @OA\Get(
     *     path="/payment-customers/{id}/",
     *     tags={"payment-customers"},
     *     summary="Get payment customer",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Payment Customer ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="To return payment customer extra data, for example, you can add cards to return cards list",
     *         in="query",
     *         name="expand",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(
     *             property="paymentUrlToken",
     *             type="string",
     *             description="Company logo URL"
     *         ),
     *         @OA\Property(
     *             property="accountId",
     *             type="integer",
     *             description="Client ID"
     *         ),
     *     ),
     *     @OA\Response(response="200", description="")
     * )
     */
}

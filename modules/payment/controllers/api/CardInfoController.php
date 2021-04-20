<?php

namespace modules\payment\controllers\api;

use api\components\rbac\Rbac;
use modules\account\models\api\Account;
use modules\account\models\api\AccountClient;
use modules\account\models\Token;
use modules\payment\models\api\AddCompanyCardForm;
use modules\payment\models\api\CardInfo;
use modules\payment\models\api\CompanyCardInfo;
use modules\payment\models\api\search\CardInfoSearch;
use Yii;
use yii\rest\IndexAction;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * Default controller for Card Info model
 */
class CardInfoController extends \api\components\AuthController
{
    /**
     * @inheritdoc
     */
    public $modelClass = 'modules\payment\models\api\CardInfo';

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
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['except'][] = 'create-card-by-token';
        $behaviors['authenticator']['except'][] = 'create-card-by-token';
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = function (IndexAction $action) {
            $searchModel = new CardInfoSearch();
            return $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        };

        unset($actions['create']);
        unset($actions['delete']);

        return $actions;
    }

    public function actionDelete($id)
    {
        /**
         * @var $card CardInfo
         */
        // Search cards among own customer's cards only
        $card = CardInfo::findOne($id);
        if (!$card) {
            // Search own company's card otherwise
            $card = CompanyCardInfo::findOne($id);
        }
        $account = \modules\account\models\Account::findOne($card->paymentCustomer->accountId);

        /**
         * @var $account Account
         */

        if (!$card) {
            throw new NotFoundHttpException();
        }

        if ($account->isCrmAdmin() && $card->isActive()) {
            throw new HttpException(403, 'Active card can not be removed.');
        }

        $this->checkAccess($this->action->id, $card);

        if (!Yii::$app->payment->removeCard($id, $account)) {
            throw new HttpException(500, 'Failed to delete credit card');
        }

        return [
            'message' => 'Credit card successfully deleted.',
        ];
    }

    /**
     * @OA\Post(
     *     path="/cards/",
     *     tags={"cards"},
     *     summary="Add new card for own account (Used for Company)",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="cardToken",
     *                 type="string",
     *                 description="Card Token Id"
     *             ),
     *             @OA\Property(
     *                 property="companyId",
     *                 type="string",
     *                 description="Company account id"
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response="201", description="Card created"),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
    public function actionCreate()
    {
        $form = new AddCompanyCardForm();
        $form->load(Yii::$app->request->post(), '');
        $card = $form->save();
        if ($form->hasErrors()) {
            return $form;
        }
        if (!$card) {
            throw new HttpException(500, 'Failed to add Credit Card');
        }
        return $card;
    }

    /**
     * @OA\Get(
     *     path="/cards/",
     *     tags={"cards"},
     *     summary="List of cards",
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
     *         description="Cards per page",
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
     *         description="Filter cards by client",
     *         in="query",
     *         name="accountId",
     *         required=false,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="To return card extra data, for example, you can add client to return client account data, cardHires to add card hires or applicants, subjects to get card subjects to add applicants list",
     *         in="query",
     *         name="expand",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),
     *     @OA\Parameter(
     *         description="Company account id",
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
     *     path="/cards/{id}/",
     *     tags={"cards"},
     *     summary="Get card data",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="card ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="To return card extra data",
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
     *     path="/cards/{id}/",
     *     tags={"cards"},
     *     summary="Close Card",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Card ID",
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
     *     path="/payment/token/card/",
     *     tags={"payment"},
     *     summary="Add new job",
     *     description="",
     *     @OA\Parameter(
     *         description="Client Payment Token",
     *         in="query",
     *         name="token",
     *         required=true,
     *         type="string"
     *     ),
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="cardToken",
     *                 type="string",
     *                 description="Stripe Credit Card Token"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="201", description="New Credit Card Attached to Client"),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
    public function actionCreateCardByToken($token)
    {
        $token = Token::findByToken($token, Token::TYPE_CLIENT_PAYMENT);
        if (!$token) {
            throw new NotFoundHttpException();
        }

        /**
         * @var $account AccountClient
         */
        $account = AccountClient::findOneWithoutRestrictions($token->accountId);
        if (!$account) {
            throw new NotFoundHttpException();
        }

        $cardToken = Yii::$app->request->post('cardToken');

        if (!$cardToken) {
            Yii::$app->response->statusCode = 422;
            return [
                [
                    'field' => 'cardToken',
                    'message' => 'Card Token is invalid',
                ],
            ];
        }

        if (
            !Yii::$app->payment->attachCardToCustomer(
                $cardToken,
                $account,
                false
            )
        ) {
            Yii::$app->response->statusCode = 422;
            return [
                [
                    'field' => 'cardToken',
                    'message' => 'Card Token is invalid',
                ],
            ];
        }

        $token->markAsUsed();
        return [
            'message' => 'Card Added Successfully',
        ];
    }
}

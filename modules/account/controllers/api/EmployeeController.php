<?php

namespace modules\account\controllers\api;

use api\components\rbac\Rbac;
use common\helpers\AccountStatusHelper;
use modules\account\models\Account;
use modules\account\models\api\AccountEmployee;
use modules\account\models\api\forms\AccountEmployeeForm;
use modules\account\models\api\search\AccountEmployeeSearch;
use modules\account\models\api\search\AccountEmployeeStatisticSearch;
use modules\account\models\Role;
use Yii;
use yii\rest\IndexAction;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class EmployeeController extends \api\components\AuthController
{
    public $modelClass = 'modules\account\models\api\AccountEmployee';

    /**
     * @inheritdoc
     */
    public function behaviorAccess()
    {
        return [
            [
                'allow' => true,
                'actions' => ['index', 'view'],
                'roles' => [Rbac::PERMISSION_BASE_B2B_PERMISSIONS],
            ],
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
            $searchModel = new AccountEmployeeSearch();
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
     *     path="/employee/",
     *     tags={"employees"},
     *     summary="List of employees",
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
     *         description="Notes per page",
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
     *     @OA\Parameter(
     *         description="Set to 1 to select only employees that have related clients",
     *         in="query",
     *         name="onlyWithClients",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="Filter by status",
     *         in="query",
     *         name="status",
     *         required=false,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="Filter by team",
     *         in="query",
     *         name="teamId",
     *         required=false,
     *         type="integer"
     *     ),
     *      @OA\Parameter(
     *         description="To return client extra data: employeeClients.client - to get list of clients related to employee, averageMargin, accountTeam.team",
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
     * @OA\Put(
     *     path="/employee/{id}/",
     *     tags={"employees"},
     *     summary="Update client",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Employee account ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="firstName",
     *                 type="string",
     *                 description="First name"
     *             ),
     *             @OA\Property(
     *                 property="lastName",
     *                 type="string",
     *                 description="Last name"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 description="Email"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 description="Set it to 2 to block user or to 1 to unblock"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
    public function actionUpdate($id)
    {
        /**
         * @var Account $account
         */
        $account = $this->getAccount($id);
        $form = new AccountEmployeeForm();
        $form->account = $account;
        $form->load(Yii::$app->request->post(), '');
        return $form->update();
    }


    /**
     * @OA\Delete(
     *     path="/employee/{id}/",
     *     tags={"employees"},
     *     summary="Remove employee",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Employee account ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Response(response="204", description=""),
     *     @OA\Response(response="404", description="")
     * )
     */
    public function actionDelete($id)
    {
        /**
         * @var Account $account
         */
        $account = $this->getAccount($id);
        $account->setStatus(AccountStatusHelper::STATUS_DELETED);
        $account->save(false);
        Yii::$app->response->setStatusCode(204);
    }

    /**
     * @param $id
     * @return array|\modules\account\models\query\AccountQuery|\yii\db\ActiveRecord|null
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function getAccount($id)
    {
        $account = AccountEmployee::findWithoutRestrictions()
            ->companyMembers()
            ->andWhere(['id' => $id])
            ->notDeleted()
            ->limit(1);

        $account = $account->one();

        if (empty($account)) {
            throw new NotFoundHttpException();
        }

        if (!\Yii::$app->user->identity->isCanEditAdmin($account)) {
            throw new ForbiddenHttpException(AccountEmployeeForm::getForbiddenMessage());
        }
        return $account;
    }

    /**
     * @OA\Get(
     *     path="/employee/{id}/",
     *     tags={"employees"},
     *     summary="Get client data",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Employee account ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="To return client extra data: employeeClients.client - to get list of clients related to employee, averageMargin",
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
     * @OA\Get(
     *     path="/employee/{id}/statistic/",
     *     tags={"employees"},
     *     summary="Get employee statistic data",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Employee account ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *      @OA\Parameter(
     *         description="Date from (m/d/Y)",
     *         in="query",
     *         name="dateFrom",
     *         required=false,
     *         type="string",
     *     ),
     *     @OA\Parameter(
     *         description="Date to (m/d/Y)",
     *         in="query",
     *         name="dateTo",
     *         required=false,
     *         type="string",
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="404", description="")
     * )
     */

    /**
     * @param int $id
     */
    public function actionStatistic(int $id)
    {
        $model = new AccountEmployeeStatisticSearch();
        $model->id = $id;
        return $model->search();
    }
}

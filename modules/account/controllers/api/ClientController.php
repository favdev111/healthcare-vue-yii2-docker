<?php

namespace modules\account\controllers\api;

use api\components\rbac\Rbac;
use modules\account\helpers\EventHelper;
use modules\account\models\api\Account;
use modules\account\models\api\AccountClient;
use modules\account\models\EmployeeClient;
use modules\account\models\api\ProfileClientSearch;
use modules\account\models\forms\ClientInvitationForm;
use modules\account\models\forms\ProfileClientForm;
use modules\labels\models\LabelRelationModel;
use Yii;
use yii\rest\IndexAction;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii2tech\csvgrid\CsvGrid;

/**
 * Default controller for User module
 */
class ClientController extends \api\components\AuthController
{
    /**
     * @inheritdoc
     */
    public $modelClass = \modules\account\models\api\AccountClient::class;

    /**
     * @inheritdoc
     */
    public function behaviorAccess()
    {
        return [
            [
                'allow' => true,
                'roles' => [Rbac::PERMISSION_BASE_B2B_PERMISSIONS],
                'actions' => [
                    'index',
                    'view',
                    'update',
                    'send-invitation',
                    'task-list-position',
                    'download-csv',
                ],
            ],
            [
                'allow' => true,
                'roles' => [Rbac::PERMISSION_CAN_CREATE_CLIENTS],
                'actions' => ['create'],
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
            $searchModel = new ProfileClientSearch();
            return $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        };

        unset(
            $actions['create'],
            $actions['update']
        );

        return $actions;
    }

    /**
     * @OA\Get(
     *     path="/clients/",
     *     tags={"clients"},
     *     summary="List of clients",
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
     *         description="Notes per page",
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
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Client flag",
     *         name="clientFlag",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Phone number",
     *         name="phoneNumber",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Email",
     *         name="email",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *
     *     @OA\Parameter(
     *         description="Search without flag",
     *         name="withoutFlag",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Search only Queued clients",
     *         name="onlyQueued",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Search all clientsn except Queued",
     *         name="exceptQueued",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Client lesson statuses",
     *         name="clientLessonStatuses[]",
     *         in="query",
     *         collectionFormat="multi",
     *         type="array",
     *         items={ "type":"integer" },
     *     ),
     *     @OA\Parameter(
     *         description="Client payment statuses",
     *         name="clientPaymentStatuses[]",
     *         in="query",
     *         collectionFormat="multi",
     *         type="array",
     *         items={ "type":"integer" },
     *     ),
     *     @OA\Parameter(
     *         description="Select client with positive balance",
     *         name="positiveBalance",
     *         in="query",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Select client with negative balance",
     *         name="negativeBalance",
     *         in="query",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Select client with zero balance",
     *         name="zeroBalance",
     *         in="query",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Select client with balance which less than 200$",
     *         name="lessThan200",
     *         in="query",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Id of related employee",
     *         name="employeeId",
     *         in="query",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Return only clients which have related employee",
     *         name="onlyWithEmployee",
     *         in="query",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *      @OA\Parameter(
     *         description="To return client extra data, for example, you can add payment to return client payment cards, employeeClient.employee, 'accountEmails','accountPhones', 'accountPhones.phoneValidation'",
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
     *     path="/clients/{id}/",
     *     tags={"clients"},
     *     summary="Get client data",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Client account ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="To return client extra data, for example, you can add payment to return client payment cards, employeeClient.employee, 'accountEmails','accountPhones', 'accountPhones.phoneValidation'",
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
     *     path="/clients/{id}/",
     *     tags={"clients"},
     *     summary="Remove client",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Client account ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Response(response="204", description=""),
     *     @OA\Response(response="404", description="")
     * )
     */

    /**
     * @OA\Post(
     *     path="/clients/",
     *     tags={"clients"},
     *     summary="Add new client",
     *     description="",
     *     security={{"Bearer":{}}},
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
     *                 property="placeId",
     *                 type="string",
     *                 description="Google place ID"
     *             ),
     *            @OA\Property(
     *                 property="gradeId",
     *                 type="integer",
     *                 description="Grade id"
     *             ),
     *             @OA\Property(
     *                 property="phoneNumbers",
     *                 type="array",
     *                 items={ "type":"object" },
     *                 description="array of example arrays: ['phoneNumber' => 12345678, 'isPrimary' => 1]"
     *             ),
     *             @OA\Property(
     *                 property="emails",
     *                 type="array",
     *                 items={ "type":"object" },
     *                 description="Emails - array of example arrays: ['email' => 12345678, 'isPrimary' => 1]"
     *             ),
     *             @OA\Property(
     *                 property="hourlyRate",
     *                 type="integer",
     *                 description="Hourly Rate"
     *             ),
     *             @OA\Property(
     *                 property="gender",
     *                 type="string",
     *                 enum={"M", "F"},
     *                 description="Gender"
     *             ),
     *             @OA\Property(
     *                 property="schoolGradeLevel",
     *                 type="string",
     *                 description="School Grade Level"
     *             ),
     *             @OA\Property(
     *                 property="schoolName",
     *                 type="string",
     *                 description="School Name"
     *             ),
     *             @OA\Property(
     *                 property="schoolGradeLevelId",
     *                 type="integer",
     *                 description="School Grade Level"
     *             ),
     *             @OA\Property(
     *                 property="subjects",
     *                 type="array",
     *                 items={ "type":"string" },
     *                 description="Grade Level"
     *             ),
     *             @OA\Property(
     *                 property="paymentAdd",
     *                 type="array",
     *                 items={ "type":"string" },
     *                 description="Array of Stripe card tokens"
     *             ),
     *             @OA\Property(
     *                 property="paymentActive",
     *                 type="string",
     *                 description="Stripe token of the active card"
     *             ),
     *             @OA\Property(
     *                 property="note",
     *                 type="string",
     *                 description="Note"
     *             ),
     *             @OA\Property(
     *                 property="balance",
     *                 type="integer",
     *                 description="Initial client balance"
     *             ),@OA\Property(
     *                 property="postPaymentAmount",
     *                 type="string",
     *                 description="post-payment Amount"
     *             ),@OA\Property(
     *                 property="postPaymentDate",
     *                 type="string",
     *                 description="post-payment Amount"
     *             ),@OA\Property(
     *                 property="flag",
     *                 type="string",
     *                 description="Flag color"
     *             ),@OA\Property(
     *                 property="startDate",
     *                 type="string",
     *                 description="startDate (Y-m-d)"
     *             ),@OA\Property(
     *                 property="employeeId",
     *                 type="integer",
     *                 description="Account id of employee related to current client"
     *             ),
     *
     *         )
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
    public function actionCreate()
    {
        /** @var \modules\account\models\forms\ProfileClientForm $model */
        $model = $this->module->model('ProfileClientForm');
        $model->scenario = 'create';
        $model->load(Yii::$app->request->post(), '');

        $isEmployeeProvided = $this->isEmployeeProvided();
        if ($isEmployeeProvided && !$this->isClientCouldBeAssigned($model) && !empty(Yii::$app->request->post('employeeId'))) {
            return $model;
        }

        $account = $model->create();

        if ($isEmployeeProvided && !empty($account)) {
            $this->updateEmployee($account, Yii::$app->request->post('employeeId'));
        }

        return $account ?? $model;
    }

    protected function isAssignedToAnotherEmployee(int $clientId): bool
    {
        $employeeId = EmployeeClient::find()->andWhere(['clientId' => $clientId])->select('employeeId')->limit(1)->scalar();
        if (!empty($employeeId) && ($employeeId != Yii::$app->user->id)) {
            return true;
        }
        return false;
    }

    /**
     * @OA\Put(
     *     path="/clients/{id}/",
     *     tags={"clients"},
     *     summary="Update client",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Client account ID",
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
     *                 property="placeId",
     *                 type="string",
     *                 description="Google place ID"
     *             ),
     *             @OA\Property(
     *                 property="phoneNumbers",
     *                 type="array",
     *                 items={ "type":"object" },
     *                 description="array of example arrays: ['phoneNumber' => 12345678, 'isPrimary' => 1]"
     *             ),
     *             @OA\Property(
     *                 property="emails",
     *                 type="array",
     *                 items={ "type":"object" },
     *                 description="Emails - array of example arrays: ['email' => 12345678, 'isPrimary' => 1]"
     *             ),
     *             @OA\Property(
     *                 property="hourlyRate",
     *                 type="integer",
     *                 description="Hourly Rate"
     *             ),
     *             @OA\Property(
     *                 property="gender",
     *                 type="string",
     *                 enum={"M", "F"},
     *                 description="Gender"
     *             ),
     *             @OA\Property(
     *                 property="schoolGradeLevel",
     *                 type="string",
     *                 description="School Grade Level"
     *             ),
     *             @OA\Property(
     *                 property="schoolName",
     *                 type="string",
     *                 description="School Name"
     *             ),
     *             @OA\Property(
     *                 property="schoolGradeLevelId",
     *                 type="integer",
     *                 description="School Grade Level"
     *             ),
     *             @OA\Property(
     *                 property="subjects",
     *                 type="array",
     *                 items={ "type":"string" },
     *                 description="Subjects"
     *             ),
     *             @OA\Property(
     *                 property="paymentAdd",
     *                 type="array",
     *                 items={ "type":"string" },
     *                 description="Array of Stripe card tokens"
     *             ),
     *             @OA\Property(
     *                 property="paymentRemove",
     *                 type="array",
     *                 items={ "type":"integer" },
     *                 description="Array of tokens ids"
     *             ),
     *             @OA\Property(
     *                 property="paymentActive",
     *                 type="string",
     *                 description="Stripe token or id of the active card"
     *             ),@OA\Property(
     *                 property="postPaymentAmount",
     *                 type="string",
     *                 description="post-payment Amount"
     *             ),@OA\Property(
     *                 property="postPaymentDate",
     *                 type="string",
     *                 description="post-payment Amount"
     *             ),@OA\Property(
     *                 property="flag",
     *                 type="string",
     *                 description="Flag color"
     *             ),@OA\Property(
     *                 property="startDate",
     *                 type="string",
     *                 description="startDate (Y-m-d)"
     *             ),@OA\Property(
     *                 property="employeeId",
     *                 type="integer",
     *                 description="Account id of employee related to current client"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
    public function actionUpdate($id)
    {
        $clientModel = AccountClient::find()->andWhere(['id' => $id])->limit(1)->one();
        if (!$clientModel) {
            throw new NotFoundHttpException();
        }

        /** @var \modules\account\models\forms\ProfileClientForm $model */
        $model = $this->module->model('ProfileClientForm');
        $model->scenario = 'update';
        $model->load(Yii::$app->request->post(), '');
        $model->accountModel = $clientModel;

        $isEmployeeProvided = $this->isEmployeeProvided();

        if (
            ($isEmployeeProvided && !$this->isClientCouldBeAssigned($model) && !empty(Yii::$app->request->post('employeeId')))
        ) {
            return $model;
        }

        $account = $model->update();

        //Do not change employees if account validation failed
        if ($isEmployeeProvided && !empty($account)) {
            $this->updateEmployee($model->accountModel, Yii::$app->request->post('employeeId'));
        }

        return $account ?? $model;
    }

    protected function isEmployeeProvided()
    {
        return array_key_exists('employeeId', Yii::$app->request->post());
    }

    protected function isClientCouldBeAssigned($model)
    {
        /**
         * @var ProfileClientForm $model
         */
        if (!$model->checkAssignPossibility()) {
            $model->addError('clientId', 'Please select blue, light blue or red flag to assign this client to employee.');
            return false;
        }
        return true;
    }

    protected function updateEmployee($account, $employeeId)
    {

        /**
         * @var AccountClient $account ;
         * @var EmployeeClient $relation
         */
        if (Yii::$app->user->identity->isCompanyEmployee() && !is_null($employeeId)) {
            //skip this method. Company employee should not be able to change employee related to another client
            //he can only unassign clients
            return;
        }
        $relation = $account->employeeClient;
        if (!empty($employeeId)) {
            //create new relation to employee if client doesn't have
            $newRelation = true;
            if (empty($relation)) {
                $relation = new EmployeeClient([
                    'employeeId' => $employeeId,
                    'clientId' => $account->id,
                    'position' => 1
                ]);
            } else {
                //update old relation if client has it
                if ($relation->employeeId === (int)$employeeId) {
                    $newRelation = false;
                }
                $relation->employeeId = $employeeId;
            }
            if (!$relation->save() && $relation->hasErrors()) {
                $account->addErrors($relation->getErrors());
            }

            if ($newRelation) {
                EventHelper::assignNewClient($account);
            }
        } else {
            //if param employeeId is empty and relation exists - remove it
            if (!empty($relation)) {
                $labelRelation = LabelRelationModel::find()->andWhere(['itemId' => $relation->clientId])->one();
                if ($labelRelation) {
                    $labelRelation->delete();
                }
                $relation->delete();
            }
        }

        $account->refresh();
    }

    /**
     * @OA\Post(
     *     path="/clients/{id}/send-invitation/",
     *     tags={"clients"},
     *     summary="Send Invitation email to client",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Client account ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     * @param $id
     * @return ClientInvitationForm
     * @throws NotFoundHttpException
     * @throws \yii\base\NotSupportedException
     */
    public function actionSendInvitation($id)
    {
        $clientModel = AccountClient::find()->andWhere(['id' => $id])->limit(1)->one();
        if (!$clientModel) {
            throw new NotFoundHttpException();
        }
        return ClientInvitationForm::send($clientModel);
    }

    /**
     * @OA\Put(
     *     path="/clients/{id}/task-list-position/",
     *     tags={"clients"},
     *     summary="Update position for task list",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="PK of employee_client table",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="position",
     *                 type="integer",
     *                 description="Item position in list"
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="403", description="Forbidden"),
     *     @OA\Response(response="400", description="Bad request")
     * )
     * @param int $id
     * @return array
     */
    public function actionTaskListPosition(int $id): array
    {
        $account = Yii::$app->user->identity;
        if (!$account->isCompanyEmployee()) {
            Yii::$app->response->statusCode = 403;
            return [
                [
                    'field' => '',
                    'message' => 'Forbidden',
                ],
            ];
        }
        $position = (int)Yii::$app->request->post('position');

        \Yii::$app->response->format = Response::FORMAT_JSON;
        if (!$position || $position < 1) {
            Yii::$app->response->statusCode = 400;
            return [
                [
                    'field' => '',
                    'message' => 'Bad request',
                ],
            ];
        }

        try {
            $employee = EmployeeClient::findOne($id);
            $employee->position = $position;
            $employee->save(false);
        } catch (\Exception $exception) {
            Yii::$app->response->statusCode = 400;
            return [
                [
                    'field' => '',
                    'message' => 'Can\'t update position',
                ],
            ];
        }

        return [
            [
                'field' => '',
                'message' => 'Position successfully updated',
            ]
        ];
    }


    /**
     * @OA\Get(
     *     path="/clients/download/csv/",
     *     tags={"clients"},
     *     summary="Download csv file with list of clients",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Client ID",
     *         in="query",
     *         name="clientId",
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
     *         description="Client flag",
     *         name="clientFlag",
     *         in="query",
     *         type="string",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         description="Search without flag",
     *         name="withoutFlag",
     *         in="query",
     *         type="string",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         description="Search only Queued clients",
     *         name="onlyQueued",
     *         in="query",
     *         type="string",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         description="Search all clientsn except Queued",
     *         name="exceptQueued",
     *         in="query",
     *         type="string",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         description="Client lesson statuses",
     *         name="clientLessonStatuses[]",
     *         in="query",
     *         collectionFormat="multi",
     *         type="array",
     *         items={ "type":"integer" },
     *     ),
     *     @OA\Parameter(
     *         description="Client payment statuses",
     *         name="clientPaymentStatuses[]",
     *         in="query",
     *         collectionFormat="multi",
     *         type="array",
     *         items={ "type":"integer" },
     *     ),
     *     @OA\Parameter(
     *         description="Select client with positive balance",
     *         name="positiveBalance",
     *         in="query",
     *         type="integer",
     *     ),
     *     @OA\Parameter(
     *         description="Select client with negative balance",
     *         name="negativeBalance",
     *         in="query",
     *         type="integer",
     *     ),
     *     @OA\Parameter(
     *         description="Select client with zero balance",
     *         name="zeroBalance",
     *         in="query",
     *         type="integer",
     *     ),
     *     @OA\Parameter(
     *         description="Select client with balance which less than 200$",
     *         name="lessThan200",
     *         in="query",
     *         type="integer",
     *     ),
     *     @OA\Parameter(
     *         description="Id of related employee",
     *         name="employeeId",
     *         in="query",
     *         type="integer",
     *     ),
     *     @OA\Parameter(
     *         description="Return only clients which have related employee",
     *         name="onlyWithEmployee",
     *         in="query",
     *         type="integer",
     *     ),
     *      @OA\Parameter(
     *         description="To return client extra data, for example,
     *          you can add payment to return client payment cards,
     *          employeeClient.employee, 'accountEmails','accountPhones', 'accountPhones.phoneValidation'",
     *         in="query",
     *         name="expand",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="403", description="Forbidden"),
     *     @OA\Response(response="404", description="Not found")
     * )
     * @return array|Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\NotSupportedException
     */
    public function actionDownloadCsv()
    {
        $searchModel = new ProfileClientSearch();

        $clients = $searchModel->search(Yii::$app->getRequest()->getQueryParams());

        if ($clients instanceof ProfileClientSearch && $clients->hasErrors()) {
            return $clients;
        }

        if (empty($clients->getModels())) {
            Yii::$app->response->statusCode = 422;
            return [
                [
                    'field' => '',
                    'message' => 'Unable to download .csv file. There are no clients available',
                ]
            ];
        }
        $exporter = new CsvGrid([
            'dataProvider' => $clients,
            'columns' => [
                [
                    'attribute' => 'firstName',
                    'value' => function ($query) {
                        return $query->profile->firstName;
                    },
                ],
                [
                    'attribute' => 'lastName',
                    'value' => function ($query) {
                        return $query->profile->lastName;
                    },
                ],
                [
                    'attribute' => 'zipcode',
                    'value' => function ($query) {
                        return $query->profile->zipCode;
                    },
                ],
                [
                    'attribute' => 'address',
                    'value' => function ($query) {
                        return $query->profile->address;
                    },
                ],
                [
                    'attribute' => 'subject',
                    'value' => function ($query) {
                        $subjects = [];
                        $subjectsOrCategories = $query->getSubjectsOrCategories();
                        foreach ($subjectsOrCategories as $subjectOrCategory) {
                            $subjects[] = $subjectOrCategory->name;
                        }
                        return empty($subjects) ? '' : implode(', ', $subjects);
                    },
                ],
            ],
        ]);

        return $exporter->export()->send('clients.csv');
    }
}

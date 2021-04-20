<?php

namespace modules\account\controllers\api;

use kartik\mpdf\Pdf;
use modules\account\helpers\ConstantsHelper;
use modules\account\models\Account;
use modules\account\models\api\AccountClient;
use modules\account\models\api\ClientRefund;
use modules\account\models\api\ClientRematch;
use yii\base\InvalidArgumentException;
use api\components\rbac\Rbac;
use modules\account\models\api\AccountReturn;
use modules\account\models\api\search\AccountReturnSearch;
use yii\rest\IndexAction;
use yii\web\HttpException;

class AccountReturnsController extends \api\components\AuthController
{
    public $modelClass = 'modules\account\models\api\AccountReturn';

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
            $searchModel = new AccountReturnSearch();
            return $searchModel->search(\Yii::$app->getRequest()->getQueryParams());
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
     *     path="/account-returns/",
     *     tags={"account-returns"},
     *     summary="Search for client's returns",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Client account ID",
     *         in="query",
     *         name="accountId",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="type of return (1 - refund, 2- rematch)",
     *         in="query",
     *         name="type",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="reasonCode",
     *         in="query",
     *         name="reasonCode",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="jobHireId",
     *         in="query",
     *         name="jobHireId",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="startDate",
     *         in="query",
     *         name="startDate",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="expand (rematchJobHires, rematchJobHires.jobHire)",
     *         in="query",
     *         name="expand",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),
     *     @OA\Response(response="201", description="Account return created"),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */

    /**
     * @OA\Post(
     *     path="/account-returns/",
     *     tags={"account-returns"},
     *     summary="Add new client return (refund or rematch)",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         @OA\Schema(
     *     @OA\Property(
     *         description="Client account ID",
     *         property="accountId",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Property(
     *         description="type of return (1 - refund, 2- rematch)",
     *         property="type",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Property(
     *         description="Some notes",
     *         property="description",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Property(
     *         description="reasonCode",
     *         property="reasonCode",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Property(
     *         description="jobHiresIds - ids of rematched job hires",
     *         property="jobHiresIds",
     *         type="array",
     *         items={ "type":"integer" },
     *     ),
     *     @OA\Property(
     *         description="jobHiresId - ids of hire related to refund",
     *         property="jobHireId",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Property(
     *         description="id of employee (for refunds)",
     *         property="employeeId",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Property(
     *         description="startDate (m/d/Y)",
     *         property="startDate",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *         )
     *     ),
     *     @OA\Response(response="201", description="Account return created"),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
    public function actionCreate()
    {
        $model = AccountReturn::getInstance(\Yii::$app->request->post());
        /**
         * @var AccountClient $clientAccount
         */
        $clientAccount = AccountClient::findOneWithoutRestrictions($model->accountId);
        if (!empty($clientAccount->employeeClient->employee->id)) {
            //save id of employee related to client in rematch moment
            //work only for rematches and refunds which works as rematch
            if (
                ($model instanceof ClientRematch)
                || (
                    ($model instanceof ClientRefund)
                    && $model->isTypeUnsatisfied()
                )
            ) {
                $model->employeeId = $clientAccount->employeeClient->employee->id;
            }
        }
        if ($model) {
            $model->save();
            return $model;
        }
        throw new HttpException(400, 'Invalid type');
    }

    /**
     * get array  with refund/rematch statistic. Structure of return: ['totalCount' => $someVal, 'reasons' => [array of reasons with calculated percent]]
     * @param int $type
     * @return array
     */
    protected function calculateStatistic(int $type): array
    {
        //getting array of reasons
        switch ($type) {
            case \modules\account\models\AccountReturn::TYPE_REMATCH:
                $reasons = ConstantsHelper::getClientRematchReasons();
                break;
            case AccountReturn::TYPE_REFUND:
                $reasons = ConstantsHelper::getClientRefundsReasons();
                break;
            default:
                throw new InvalidArgumentException('Invalid type');
                break;
        }

        $totalSearch = new AccountReturnSearch();
        //https://heytutor.atlassian.net/browse/HT-878
        $totalSearch->userStatisticStartDateCondition = true;
        $totalSearch->onlyWithHires = true;
        $totalProvider = $totalSearch->search(\Yii::$app->request->get());
        $totalCount = $totalProvider->totalCount;
        if (empty($totalCount)) {
            return ['totalCount' => 0, 'reasons' => []];
        }

        //getting percent of each reason
        foreach ($reasons as &$reason) {
            $reasonSearch = (new AccountReturnSearch());
            $reasonSearch->reasonCode = $reason['id'];
            //https://heytutor.atlassian.net/browse/HT-878
            $reasonSearch->userStatisticStartDateCondition = true;
            $reasonSearch->onlyWithHires = true;
            $reasonProvider = $reasonSearch->search(\Yii::$app->request->get());
            $count = $reasonProvider->totalCount;
            $percent = (float)number_format(($count / $totalCount * 100), 2);
            $reason['percent'] = $percent;
            $reason['count'] = $count;
        }
        return ['totalCount' => $totalCount, 'reasons' => $reasons];
    }


    /**
     * @OA\Get(
     *     path="/account-returns/statistic/",
     *     tags={"account-returns"},
     *     summary="Search for statistic of client's returns",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Client account ID",
     *         in="query",
     *         name="accountId",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Tutor id",
     *         in="query",
     *         name="tutorId",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Employee id",
     *         in="query",
     *         name="employeeId",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="type of return (1 - refund, 2- rematch)",
     *         in="query",
     *         name="type",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="reasonCode",
     *         in="query",
     *         name="reasonCode",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="jobHireId",
     *         in="query",
     *         name="jobHireId",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Date of create from(m/d/Y)",
     *         in="query",
     *         name="dateFrom",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Date of create to(m/d/Y)",
     *         in="query",
     *         name="dateTo",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
    public function actionStatistic()
    {
        $typeOfReturn = (int)\Yii::$app->request->get('type');
        if (!in_array($typeOfReturn, AccountReturn::$typesArray)) {
            throw new HttpException(400, 'Invalid type provided');
        }

        return $this->calculateStatistic($typeOfReturn);
    }

    /**
     * @OA\Get(
     *     path="/account-returns/statistic-download/",
     *     tags={"account-returns"},
     *     summary="Download pdf statistic of client's returns",
     *     description="Create and download pdf statistic with incoming params from filter form",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Client account ID",
     *         in="query",
     *         name="accountId",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Employee id",
     *         in="query",
     *         name="employeeId",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="type of return (1 - refund, 2- rematch)",
     *         in="query",
     *         name="type",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Date of create from(m/d/Y)",
     *         in="query",
     *         name="dateFrom",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         description="Date of create to(m/d/Y)",
     *         in="query",
     *         name="dateTo",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors"),
     *     @OA\Response(response="400", description="Bad request")
     * )
     */
    public function actionDownload()
    {
        if (!\Yii::$app->user->identity->isCrmAdmin()) {
            \Yii::$app->response->setStatusCode(403);
            return ['message' => 'Forbidden'];
        }

        $typeOfReturn = (int)\Yii::$app->request->get('type');
        if (!in_array($typeOfReturn, AccountReturn::$typesArray)) {
            \Yii::$app->response->setStatusCode(400);
            return ['message' => 'Invalid type provided'];
        }

        try {
            $fileName = $this->pdfFileName(\Yii::$app->request->getQueryParams());


            $statisticData = $this->calculateStatistic($typeOfReturn);
            if ($statisticData['totalCount'] === 0) {
                \Yii::$app->response->setStatusCode(422);
                return ['message' => 'Unable to download .pdf file. There are no statistic data available'];
            }
            $content = $this->renderPartial('refundRematch', ['statistics' => $statisticData]);
            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'format' => Pdf::FORMAT_A4,
                'orientation' => Pdf::ORIENT_PORTRAIT,
                'destination' => Pdf::DEST_STRING,
                'content' => $content,
                'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
                'cssInline' => '.kv-heading-1{font-size:18px}',
                'options' => ['title' => 'RefundRematch'],
            ]);
        } catch (\Exception $exception) {
            \Yii::$app->response->setStatusCode(400);
            return ['message' => 'Can\'t calculate statistic','errorText' => $exception->getMessage()];
        }

        return \Yii::$app->response->sendContentAsFile(
            $pdf->render(),
            $fileName . '.pdf',
            [
                'mimeType' => 'application/pdf',
                'inline' => true,
            ]
        );
    }

    /**
     * @param array $params
     * @return string
     */
    private function pdfFileName(array $params): string
    {
        $fileName = 'Rematch_statistic_';
        $currentDate = '06_01_2019-' . date('m_d_Y');
        if (isset($params['accountId'])) {
            $account = Account::findOne($params['accountId'])->profile;
            $params['accountId'] = '_st_' .
            isset($account->firstName) ? $account->firstName : 'unnamed' . '_' .
            isset($account->lastName) ? $account->lastName : 'unnamed' ;
        }

        if (isset($params['employeeId'])) {
            $account = Account::findOne($params['employeeId'])->profile;
            $params['employeeId'] = '_emp_' .
            isset($account->firstName) ? $account->firstName : 'unnamed' . '_' .
            isset($account->lastName) ? $account->lastName : 'unnamed' ;
        }

        if (isset($params['dateFrom'])) {
            $params['dateFrom'] = date('m_d_Y', strtotime($params['dateFrom']));
        }

        if (isset($params['dateTo'])) {
            $params['dateTo'] = date('m_d_Y', strtotime($params['dateTo']));
        }

        /** All filters */
        if (isset($params['accountId'], $params['employeeId'], $params['dateFrom'], $params['dateTo'])) {
            return $fileName . $params['dateFrom'] . '-' . $params['dateTo'] . $params['employeeId'] . $params['accountId'];
        }

        /** Account and Employee set */
        if (isset($params['accountId'], $params['employeeId'])) {
            return $fileName . $currentDate . $params['employeeId'] . $params['accountId'];
        }

        /** Only Employee and date */
        if (isset($params['employeeId'], $params['dateFrom'], $params['dateTo'])) {
            return $fileName . $params['dateFrom'] . '-' . $params['dateTo'] . $params['employeeId'];
        }

        /** Only Employee */
        if (isset($params['employeeId'])) {
            return $fileName . $currentDate . $params['employeeId'];
        }

        /** Only Account and date */
        if (isset($params['accountId'], $params['dateFrom'], $params['dateTo'])) {
            return $fileName . $params['dateFrom'] . '-' . $params['dateTo'] . $params['accountId'];
        }

        /** Only Account */
        if (isset($params['accountId'])) {
            return $fileName . $currentDate . $params['accountId'];
        }

        /** Only date set */
        if (isset($params['dateFrom'], $params['dateTo'])) {
            return $fileName . $params['dateFrom'] . '-' . $params['dateTo'];
        }

        /** no filters */
        return $fileName . $currentDate;
    }
}

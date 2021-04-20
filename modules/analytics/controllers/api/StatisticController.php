<?php

namespace modules\analytics\controllers\api;

use common\helpers\Role;
use modules\analytics\models\search\KpiSearch;
use modules\analytics\models\search\StatisticSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\helpers\ArrayHelper;

/**
 * Statistic controller
 */
class StatisticController extends \api\components\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'authenticator' => [
                    'class' => HttpBearerAuth::class,
                    'except' => [
                        'options',
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => [Role::ROLE_CRM_ADMIN],
                        ],
                    ],
                    'except' => [
                        'options',
                    ],
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }

    /**
     * @OA\Get(
     *     path="/statistics/kpi/",
     *     tags={"statistic"},
     *     summary="KPI Info",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="List of Subject IDs (Multiple IDs can be added by adding more `&subjectOrCategoryIds[]=` parts",
     *         in="query",
     *         name="subjectOrCategoryIds[]",
     *         collectionFormat="multi",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),
     *     @OA\Parameter(
     *         description="From Date",
     *         in="query",
     *         name="dateFrom",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="To Date",
     *         in="query",
     *         name="dateTo",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="Add addtional data to response (for testing)",
     *         in="query",
     *         name="additionalData",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Example extended response",
     *          @OA\Schema(
     *              @OA\Property(
     *                  description="Total Sum got from Clients (who were registered in selected Date Range)",
     *                  property="cashBasis",
     *                  type="number",
     *              ),
     *              @OA\Property(
     *                  description="Total Sum paid for Lessons to Company from Clients (who were registered in selected Date Range)",
     *                  property="accrualBasis",
     *                  type="number",
     *              ),
     *          )
     *     ),
     * )
     */
    public function actionKpi()
    {
        $kpiSearchForm = new KpiSearch();
        $result = $kpiSearchForm->search(Yii::$app->request->queryParams);
        if ($result === false && $kpiSearchForm->hasErrors()) {
            // Return model errors if any
            return $kpiSearchForm;
        }
        return $result;
    }

    /**
     * @OA\Get(
     *     path="/statistics/",
     *     tags={"statistic"},
     *     summary="Statistic Info",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="List of Subject IDs (Multiple IDs can be added by adding more `&subjectOrCategoryIds[]=` parts",
     *         in="query",
     *         name="subjectOrCategoryIds[]",
     *         required=false,
     *         type="string",
     *     ),
     *     @OA\Parameter(
     *         description="From Date",
     *         in="query",
     *         name="dateFrom",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="To Date",
     *         in="query",
     *         name="dateTo",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="Has active lessons within days",
     *         in="query",
     *         name="hasLessonWithinDays",
     *         required=false,
     *         type="number"
     *     ),
     *     @OA\Parameter(
     *         description="Filter by First Name or Laste Name",
     *         in="query",
     *         name="query",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="Expand",
     *         in="query",
     *         name="expand",
     *         required=false,
     *         type="array",
     *         collectionFormat="csv",
     *         @OA\Items(
     *             type="string",
     *             enum={"client", "transaction", "client.employeeClient"}
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Example extended response",
     *     ),
     * )
     */
    public function actionIndex()
    {
        $kpiSearchForm = new StatisticSearch();
        $result = $kpiSearchForm->search(Yii::$app->request->queryParams);
        if ($result === false && $kpiSearchForm->hasErrors()) {
            // Return model errors if any
            return $kpiSearchForm;
        }
        return $result;
    }
}

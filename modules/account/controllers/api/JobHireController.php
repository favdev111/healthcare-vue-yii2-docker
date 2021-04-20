<?php

namespace modules\account\controllers\api;

use api\components\rbac\Rbac;
use common\components\ActiveQuery;
use common\helpers\Role;
use modules\account\models\api\Account;
use modules\account\models\api\Job;
use modules\account\models\api\search\JobHireSearch;
use modules\account\models\JobHire;
use modules\account\models\Rate;
use modules\payment\Module;
use Yii;
use yii\db\Expression;
use yii\rest\IndexAction;
use yii\web\ForbiddenHttpException;

/**
 * Default controller for Job Hire model
 */
class JobHireController extends \api\components\AuthController
{
    /**
     * @inheritdoc
     */
    public $modelClass = 'modules\account\models\api\JobHire';

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
        $actions['index']['prepareDataProvider'] = function (IndexAction $action) {
            $searchModel = new JobHireSearch();
            return $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        };

        return $actions;
    }

    /**
     * @OA\Get(
     *     path="/job-hires/",
     *     tags={"job-hires"},
     *     summary="List of job hires",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Filter job hires by client",
     *         in="query",
     *         name="studentId",
     *         required=false,
     *         type="integer"
     *     ),
     *      @OA\Parameter(
     *         description="Status",
     *         in="query",
     *         name="status[]",
     *         type="array",
     *         collectionFormat="multi",
     *         items={ "type":"integer" },
     *     ),
     *     @OA\Parameter(
     *         description="Filter job hires by job",
     *         in="query",
     *         name="jobId",
     *         required=false,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="Page number",
     *         in="query",
     *         name="page",
     *         required=false,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="Job Hires per page",
     *         in="query",
     *         name="per-page",
     *         required=false,
     *         type="integer"
     *     ),
     *       @OA\Parameter(
     *         description="Job zip code",
     *         in="query",
     *         name="jobZipCode",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="filter by field CreatedAt dateFrom(m/d/Y)",
     *         in="query",
     *         name="dateFrom",
     *         required=false,
     *         type="string"
     *     ),
     *      @OA\Parameter(
     *         description="filter by field CreatedAt dateTo(m/d/Y)",
     *         in="query",
     *         name="dateTo",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="To return job hire extra data, for example, you can add student to return client data, tutor to add tutor data, jobSubjects to get list of job subjects or job to get job details, responsible, isAutomatch, changeList",
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
     *     path="/job-hires/{id}/",
     *     tags={"job-hires"},
     *     summary="Get job hire data",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="job hire ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="To return job hire extra data, for example, you can add student to return client data, tutor to add tutor data, jobSubjects to get list of job subjects or job to get job details,responsible",
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
     *     path="/job-hires/{id}/",
     *     tags={"job-hires"},
     *     summary="Remove Job Hire",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Job Hire ID",
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
     *     path="/job-hires/",
     *     tags={"job-hires"},
     *     summary="Add new job",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="jobId",
     *                 type="integer",
     *                 description="Job ID"
     *             ),
     *             @OA\Property(
     *                 property="tutorId",
     *                 type="integer",
     *                 description="Tutor ID"
     *             ),
     *             @OA\Property(
     *                 property="price",
     *                 type="number",
     *                 description="price"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 description="Job Hire Status (1 - Hired, 0 - Declined)",
     *                 enum={"1", "0"}
     *             ),
     *              @OA\Property(
     *                 property="shareContactInfo",
     *                 type="integer",
     *                 description="Share client contact info in message to tutor",
     *                 enum={"1", "0"}
     *             ),
     *             @OA\Property(
     *                 property="isManual",
     *                 type="integer",
     *                 description="Created using pop-up for manual relationship creation",
     *                 enum={"1", "0"}
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="201", description="Job Hire created"),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */

    /**
     * @OA\Put(
     *     path="/job-hires/{id}/",
     *     tags={"job-hires"},
     *     summary="Update job",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="job ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="jobId",
     *                 type="integer",
     *                 description="Job ID"
     *             ),
     *             @OA\Property(
     *                 property="tutorId",
     *                 type="integer",
     *                 description="Tutor ID"
     *             ),
     *             @OA\Property(
     *                 property="price",
     *                 type="number",
     *                 description="price"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 description="Job Hire Status (1 - Hired, 0 - Declined)",
     *                 enum={"1", "0"}
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */

    /**
     * @OA\Get(
     *     path="/job-hires/averages/",
     *     tags={"job-hires"},
     *     summary="Get job hire averages",
     *     description="",
     *     security={{"Bearer":{}}},
     *      @OA\Parameter(
     *         description="From date filter (Y-m-d)",
     *         in="query",
     *         name="fromDate",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="To date filter (Y-m-d)",
     *         in="query",
     *         name="toDate",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="Search by keyword",
     *         in="query",
     *         name="query",
     *         required=false,
     *         type="string",
     *         items={ "type":"string" },
     *     ),
     *
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="404", description="")
     * )
     */
    public function actionAverages()
    {
        /**
         * @var $paymentModule Module
         */
        $paymentModule = Yii::$app->getModule('payment');
        $identity = Yii::$app->user->identity;
        $commission = $identity->isCrmAdmin() ? $identity->commission : 0;
        $commissionCoefficient = $paymentModule->getCompanyCommissionCoefficientForOfferOrHire($commission);

        $queryParams = Yii::$app->getRequest()->getQueryParams();
        $provider = (new JobHireSearch());
        //if validation failed search() returns object of class JobHireSearch
        $provider = $provider->search($queryParams);

        if ($provider instanceof JobHireSearch) {
            return $provider;
        }

        /**
         * @var $query ActiveQuery
         */
        $query = $provider->query;

        $query->joinWith('student.rate');
        $query->joinWith('job');

        $revenuePart = 'IF((' . Job::tableName() . '.billRate is not null), ' . Job::tableName() . '.billRate, ' . Rate::tableName() . '.hourlyRate)';
        $costPart = 'ROUND(' . JobHire::tableName() . '.price * ' . $commissionCoefficient . ')';
        $query->addSelect([
            'revenue' => new Expression('AVG(' . $revenuePart . ')'),
            'cost' => new Expression('AVG(' . $costPart . ')'),
            'margin' => new Expression('AVG(((' . $revenuePart . ' - ' . $costPart . ') / ' . $revenuePart . ') * 100)'),
        ]);

        $result = $query->createCommand()->queryOne();

        foreach ($result as &$column) {
            $column = (float)$column;
        }
        return $result;
    }
}

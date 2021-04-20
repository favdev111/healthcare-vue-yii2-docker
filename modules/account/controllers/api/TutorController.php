<?php

namespace modules\account\controllers\api;

use api\components\rbac\Rbac;
use Codeception\Util\HttpCode;
use common\helpers\QueueHelper;
use common\helpers\Role;
use common\models\ProcessedEvent;
use modules\account\models\api\Job;
use modules\account\models\api\search\TutorSearch;
use modules\account\models\api\Tutor;
use modules\account\models\IgnoredTutorsJob;
use modules\account\Module;
use Yii;
use yii\base\Exception;
use yii\db\Expression;
use yii\rest\IndexAction;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * Default controller for Tutor model
 */
class TutorController extends \api\components\AuthController
{
    /**
     * @inheritdoc
     */
    public $modelClass = 'modules\account\models\api\Tutor';

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
            $searchModel = new TutorSearch();
            return $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        };

        return $actions;
    }

    /**
     * @OA\Post(
     *     path="/tutors/send-job-link/",
     *     tags={"tutors"},
     *     summary="Send job link",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="tutorId",
     *                 type="integer",
     *                 description="Id of a tutor"
     *             ),
     *     @OA\Property(
     *                 property="jobId",
     *                 type="integer",
     *                 description="Id of a job"
     *             )
     *         )
     *     ),
     *   @OA\Response(response="200", description="")
     * )
     *
     */

    public function actionSendJobLink()
    {
        $jobId = (int)\Yii::$app->request->post('jobId');
        $tutorId = (int)\Yii::$app->request->post('tutorId');

        $job = $this->findJob($jobId);
        if ($job->originJobId) {
            $ignoredTutorExist = IgnoredTutorsJob::find()
                ->andWhere(['tutorId' => $tutorId])
                ->andWhere(['originJobId' => $job->originJobId])
                ->exists();

            if ($ignoredTutorExist) {
                return $this->processError('tutorId', 'Tutor not found.');
            }
        }
        $tutorExists = Tutor::find()->andWhere(['id' => $tutorId])->exists();
        if (!$tutorExists) {
            return $this->processError('tutorId', 'Tutor not found.');
        }

        if ($job->isTutorNotified($tutorId)) {
            return $this->processError('tutorId', 'Tutor has already been notified about this job.');
        }

        /**
         * @var Module $moduleAccount
         */
        $moduleAccount = Yii::$app->getModule('account');
        $data = [
            'from' => \Yii::$app->user->id,
        ];

        $moduleAccount->eventNewJobPosted($job, $tutorId, false);

        if ($job->tutorNotifiedAboutNewJob($tutorId, $data)) {
            Yii::$app->response->statusCode = HttpCode::OK;
        } else {
            return $this->processError('jobId', 'Tutor has already been notified about this job.', 500);
        }

        $job->updateCountNotificationCounter(1);
    }

    protected function processError(string $field, string $errorMessage, int $responseCode = 422): array
    {
        Yii::$app->response->statusCode = $responseCode;
        return [['field' => $field, 'message' => $errorMessage]];
    }

    /**
     * @param int $id
     * @return Job
     * @throws NotFoundHttpException
     */
    protected function findJob(int $id): Job
    {
        $job = Job::findOne($id);
        if (empty($job)) {
            throw new NotFoundHttpException('Job not found.');
        }
        return $job;
    }

    /**
     * @OA\Post(
     *     path="/tutors/check-notification-about-job/",
     *     tags={"tutors"},
     *     summary="Check notification about job",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="tutorIds",
     *                 type="array",
     *                 items={ "type":"integer" },
     *                 description="Ids of a tutor"
     *             ),
     *     @OA\Property(
     *                 property="jobId",
     *                 type="integer",
     *                 description="Id of a job"
     *             )
     *         )
     *     ),
     *   @OA\Response(response="200", description="")
     * )
     *
     */
    public function actionCheckNotificationAboutJob()
    {
        $tutorIds = \Yii::$app->request->post('tutorIds');
        $jobId = (int)\Yii::$app->request->post('jobId');

        $job = $this->findJob($jobId);

        $result = [];
        foreach ($tutorIds as &$tutorId) {
            $tutorId = (int)$tutorId;

            if ($job->isTutorNotified($tutorId)) {
                $result[] = $tutorId;
            }
        }
        $ignoredTutorsId = IgnoredTutorsJob::find()
            ->select(['tutorId'])
            ->andWhere(['originJobId' => $job->originJobId])
            ->column();

        if ($ignoredTutorsId) {
            $result = array_merge(
                $result,
                array_map(function ($item) {
                    return (int)$item;
                }, $ignoredTutorsId)
            );
        }
        return $result;
    }

    /**
     * @OA\Get(
     *     path="/tutors/",
     *     tags={"tutors"},
     *     summary="List of tutors",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Use Elastic Search",
     *         in="query",
     *         name="useElastic",
     *         required=false,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="Exclude tutors with 'Hide on marketplace' flag (1 - by default)",
     *         in="query",
     *         name="excludeHiddenSearch",
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
     *         description="Tutors per page",
     *         in="query",
     *         name="per-page",
     *         required=false,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="Tutors name",
     *         in="query",
     *         name="fullName",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="Subjects",
     *         in="query",
     *         name="subjects",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),
     *     @OA\Parameter(
     *         description="Zip Code",
     *         in="query",
     *         name="zipCode",
     *         required=false,
     *         type="string",
     *     ),
     *   @OA\Parameter(
     *         description="Distance filter. Example values: 15mi, 20mi, 150mi",
     *         in="query",
     *         name="distance",
     *         required=false,
     *         type="string",
     *     ),
     *     @OA\Parameter(
     *         description="Address",
     *         in="query",
     *         name="address",
     *         required=false,
     *         type="string",
     *     ),
     *     @OA\Parameter(
     *         description="Gender (M - male, F - female, B - both)",
     *         in="query",
     *         name="gender",
     *         required=false,
     *         type="string",
     *     ),
     *     @OA\Parameter(
     *         description="min Age",
     *         in="query",
     *         name="fromAge",
     *         required=false,
     *         type="integer",
     *     ),
     *     @OA\Parameter(
     *         description="max Age",
     *         in="query",
     *         name="toAge",
     *         required=false,
     *         type="integer",
     *     ),
     *     @OA\Parameter(
     *         description="Rate from",
     *         in="query",
     *         name="fromRate",
     *         required=false,
     *         type="integer",
     *     ),
     *      @OA\Parameter(
     *         description="Rate to",
     *         in="query",
     *         name="toRate",
     *         required=false,
     *         type="integer",
     *     ),
     *     @OA\Parameter(
     *         description="To return tutor extra data, for example reviews, rate, subjects, educations, jobs",
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
     *     path="/tutors/{id}/",
     *     tags={"tutors"},
     *     summary="Get tutor data",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="tutor ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="To return tutor extra data, for example reviews, rate, subjects, educations, jobs",
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
}

<?php

namespace modules\account\controllers\api;

use api\components\rbac\Rbac;
use DateTime;
use modules\account\models\api\Job;
use modules\account\models\Job as MainJob;
use modules\account\models\api\search\JobSearch;
use modules\account\models\api\search\XmlJobSearch;
use modules\account\models\IgnoredTutorsJob;
use modules\account\models\JobHire;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\rest\IndexAction;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Default controller for Job model
 */
class JobController extends \api\components\AuthController
{
    /**
     * @inheritdoc
     */
    public $modelClass = 'modules\account\models\api\Job';

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => 'yii\filters\PageCache',
                'only' => ['xml'],
                'duration' => 60 * 60 * 3,
                'variations' => [
                    Yii::$app->request->get(),
                ],
            ],
        ]);
    }

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
            $searchModel = new JobSearch();
            return $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        };

        return $actions;
    }

    public function actionReopen($id)
    {
        /**
         * @var $model Job
         */
        $model = Job::find()->andWhere(['close' => true])->andWhere(['id' => $id])->one();

        if (!$model) {
            throw new NotFoundHttpException('No such closed job found.');
        }

        $this->checkAccess($this->id, $model);

        $model->close = false;
        if ($model->save() === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        /**
         * @var $model Job
         */
        parent::checkAccess($action, $model, $params);
        switch ($action) {
            case 'update':
                $post = Yii::$app->request->post();
                if (
                    ($model->originJobId && $model->repostedJobId)
                    && !(
                        count($post) == 1
                        && (
                            isset($post['isAutomatchEnabled'])
                            || isset($post['billRate'])
                        )
                    )
                ) {
                    throw new ForbiddenHttpException('Sorry, reposted job can not be edited');
                }

                if (
                    $model->close
                    && (
                        count($post) > 1 || !isset($post['billRate'])
                    )
                ) {
                    throw new ForbiddenHttpException('Sorry, closed job can not be edited');
                }

                //except forceSendingNotification option
                if (
                    $model->getApplicants()->exists()
                    && !(
                        count($post) == 1
                        && (
                            isset($post['forceSendingNotification'])
                            || isset($post['isAutomatchEnabled'])
                            || isset($post['billRate'])
                        )
                    )
                ) {
                    $message = 'This job is active, you can not edit job with the applicants';
                    throw new ForbiddenHttpException($message);
                }
                break;
        }
    }

    /**
     * @OA\Get(
     *     path="/jobs/",
     *     tags={"jobs"},
     *     summary="List of jobs",
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
     *         description="Jobs per page",
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
     *         description="Show only closed jobs or only non-closed jobs",
     *         in="query",
     *         name="close",
     *         required=false,
     *         type="integer",
     *         enum={"1", "0"}
     *     ),
     *     @OA\Parameter(
     *         description="In case 1:Job has isAutomatch enabled flag OR has automatch result. In case 0: hos no flag AND has no automatch result.",
     *         in="query",
     *         name="isAutomatched",
     *         required=false,
     *         type="integer",
     *         enum={"1", "0"}
     *     ),
     *     @OA\Parameter(
     *         description="Sort job. Example: (createdAt, updatedAt, id) - ASC; (-createdAt, -updatedAt, -id) - DESC",
     *         in="query",
     *         name="sort",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="Filter jobs by client",
     *         in="query",
     *         name="accountId",
     *         required=false,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="To return job extra data, for example, you can add client to return client account data,
     *          jobHires to add job hires or applicants, subjects to get job subjects to add applicants list,
     *          jobOffers to get offers list, latestJobOffer, availabilityData, coordinates to get client coordinates,
     *      manualApplicants, changelist",
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
     *     path="/jobs/xml/",
     *     tags={"jobs"},
     *     summary="Export jobs to xml",
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
     *         description="Jobs per page",
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
     *         description="Filter jobs by client",
     *         in="query",
     *         name="accountId",
     *         required=false,
     *         type="integer"
     *     ),
     *     @OA\Response(response="200", description="")
     * )
     */
    public function actionXml()
    {
        $defaultPageSize = XmlJobSearch::XML_DEFAULT_PAGE_SIZE;
        $xmlDescription = <<< DESC
HeyTutor Jobs.
Pagination:
This document supports pagination.
By default displays first page. You can change displayed page using "page" parameter in query string.
Example:http://winitclinic.com/api/xml/?page=3
Default page size equal {$defaultPageSize}.
You can change count items displayed on one page using "per-page" parameter in query string.
Example:http://winitclinic.com/api/xml/?per-page=20
Field list:
internalId - id of a job in HeyTutor system
title - job title
studentName - name of a student
zipCode - zip code related with job (where student looking for tutor)
description - job description
city - name of a city related to job (based on zip code)
stateName - name of state related to job (based on zip code)
studentGrade - grade of student (Elementary, Middle school, High school, College, Adult)
tutorLocation - the place in which student prefers to conduct lessons (At home, Library/public place, Tutor's location)
pubDate - date create of job
link - url to job detail page
guid - A string of character that is unique to designate this item.
DESC;

        $response = Yii::$app->getResponse();
        $headers = $response->getHeaders();
        $headers->set('Content-Type', 'application/rss+xml; charset=utf-8');

        $searchModel = new XmlJobSearch();
        $dataProvider = $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        $response->content = \Zelenin\yii\extensions\Rss\RssView::widget([
            'dataProvider' => $dataProvider,
            'channel' => [
                'title' => 'HeyTutor Jobs',
                'link' => Url::toRoute('/xml', true),
                'description' => $xmlDescription,
                'language' => Yii::$app->language
            ],
            'items' => [
                'internalId' => function ($model, $widget) {
                    return (string)$model->id;
                },
                'title' => function ($model, $widget) {
                    return $model->getNameWithLocationAndSubject();
                },
                'studentName' => function ($model, $widget) {
                    return $model->account->profile->fullName();
                },
                'zipCode' => function ($model, $widget) {
                    return (string)$model->zipCode;
                },
                'description' => function ($model, $widget, \Zelenin\Feed $feed) {
                    return $model->displayedDescription;
                },
                'city' => function ($model, $widget) {
                    return $model->city->name;
                },
                'stateName' => function ($model, $widget) {
                    return $model->city->stateName;
                },
                'studentGrade' => function ($model, $widget) {
                    return $model->getGrade();
                },
                'tutorLocation' => function ($model, $widget) {
                    return $model->getLessonOccurText();
                },
                'pubDate' => function ($model, $widget) {
                    return DateTime::createFromFormat(
                        Yii::$app->formatter->MYSQL_DATETIME,
                        $model->createdAt
                    )->format(DateTime::RSS);
                },
                'link' => function ($model, $widget) {
                    return $model->getPublicLink();
                },
                'guid' => function ($model, $widget) {
                    return $model->getPublicLink();
                }
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/jobs/{id}/",
     *     tags={"jobs"},
     *     summary="Get job data",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="job ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="To return job extra data, for example, you can add client to return client account data,
     *          jobHires to add job hires, subjects to get job subjects or applicants to add applicants list,
     *     jobOffers to get offers list, latestJobOffer, availabilityData, coordinates to get client coordinates,
     *      manualApplicants",
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
     *     path="/jobs/{id}/",
     *     tags={"jobs"},
     *     summary="Close Job",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Job ID",
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
     *     path="/jobs/",
     *     tags={"jobs"},
     *     summary="Add new job",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="accountId",
     *                 type="integer",
     *                 description="Client ID"
     *             ),
     *             @OA\Property(
     *                 property="studentGrade",
     *                 type="integer",
     *                 description="Student Grade key"
     *             ),
     *             @OA\Property(
     *                 property="lessonOccur",
     *                 type="integer",
     *                 description="Lesson Occur place key"
     *             ),
     *             @OA\Property(
     *                 property="forceSendingNotification",
     *                 type="integer",
     *                 description="Start new job posted notification sending even if applicatns count more than limit."
     *             ),
     *             @OA\Property(
     *                 property="isAutomatchEnabled",
     *                 type="integer",
     *                 description="Turn on/off automatch for this job"
     *             ),
     *             @OA\Property(
     *                 property="gender",
     *                 type="integer",
     *                 enum={"M", "F", "B"},
     *                 description="Gender key"
     *             ),
     *             @OA\Property(
     *                 property="hourlyRateTo",
     *                 type="integer",
     *                 description="Hourly Rate range to"
     *             ),
     *             @OA\Property(
     *                 property="startLesson",
     *                 type="integer",
     *                 description="Start Lesson Time key"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="Job Description"
     *             ),
     *             @OA\Property(
     *                 property="zipCode",
     *                 type="integer",
     *                 description="Job ZIP Code"
     *             ),
     *             @OA\Property(
     *                 property="subjects",
     *                 type="array",
     *                 items={ "type":"integer" },
     *                 description="Subjects"
     *             ),
     *             @OA\Property(
     *                 property="billRate",
     *                 type="integer",
     *                 description="Bill Rate"
     *             ),
     *            @OA\Property(
     *                 property="availabilityArray",
     *                 type="object",
     *                 description="availability Array"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="201", description="Job created"),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */

    /**
     * @OA\Put(
     *     path="/jobs/reopen/{id}/",
     *     tags={"jobs"},
     *     summary="Reopen job",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="job ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Response(response="201", description="Job reopened"),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */

    /**
     * @OA\Put(
     *     path="/jobs/{id}/",
     *     tags={"jobs"},
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
     *                 property="accountId",
     *                 type="integer",
     *                 description="Client ID"
     *             ),
     *             @OA\Property(
     *                 property="isAutomatchEnabled",
     *                 type="integer",
     *                 description="Turn on/off automatch for this job"
     *             ),
     *             @OA\Property(
     *                 property="studentGrade",
     *                 type="integer",
     *                 description="Student Grade key"
     *             ),
     *             @OA\Property(
     *                 property="lessonOccur",
     *                 type="integer",
     *                 description="Lesson Occur place key"
     *             ),
     *             @OA\Property(
     *                 property="gender",
     *                 type="integer",
     *                 enum={"M", "F", "B"},
     *                 description="Gender key"
     *             ),
     *             @OA\Property(
     *                 property="hourlyRateTo",
     *                 type="integer",
     *                 description="Hourly Rate range to"
     *             ),
     *             @OA\Property(
     *                 property="startLesson",
     *                 type="integer",
     *                 description="Start Lesson Time key"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="Job Description"
     *             ),
     *             @OA\Property(
     *                 property="zipCode",
     *                 type="integer",
     *                 description="Job ZIP Code"
     *             ),
     *             @OA\Property(
     *                 property="close",
     *                 type="integer",
     *                 enum={"1", "0"},
     *                 description="Close Status of Job"
     *             ),
     *             @OA\Property(
     *                 property="subjects",
     *                 type="array",
     *                 items={ "type":"integer" },
     *                 description="Subjects"
     *             ),
     *             @OA\Property(
     *                 property="availabilityArray",
     *                 type="object",
     *                 description="availability Array"
     *             ),
     *             @OA\Property(
     *                 property="billRate",
     *                 type="integer",
     *                 description="Bill Rate"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */

    /**
     * @OA\Put(
     *     path="/jobs/repost/{id}/",
     *     tags={"jobs"},
     *     summary="Repost a job",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Job ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="To return job extra data, for example, you can add client to return client account data,
     *          jobHires to add job hires or applicants, subjects to get job subjects to add applicants list,
     *          jobOffers to get offers list, latestJobOffer, availabilityData, coordinates to get client coordinates",
     *         in="query",
     *         name="expand",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),
     *     @OA\Response(response="201", description="Job reposted"),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="400", description="Bad request"),
     *     @OA\Response(response="404", description="Not found"),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     * @param int $id
     * @return array|MainJob|\yii\db\ActiveRecord|null
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionRepost(int $id)
    {
        try {
            return Job::repostJob($id);
        } catch (NotFoundHttpException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (\Exception $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
    }
}

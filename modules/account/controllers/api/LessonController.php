<?php

namespace modules\account\controllers\api;

use api\components\rbac\Rbac;
use common\components\ActiveQuery;
use common\helpers\Role;
use kartik\mpdf\Pdf;
use modules\account\models\api\search\LessonSearch;
use modules\account\models\ClientBalanceTransaction;
use modules\account\models\Lesson;
use Yii;
use yii\db\Expression;
use yii\rest\IndexAction;
use yii\web\NotFoundHttpException;

class LessonController extends \api\components\AuthController
{
    public $modelClass = 'modules\account\models\api\Lesson';

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
            $searchModel = new LessonSearch();
            return $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        };

        unset(
            $actions['update']
        );

        return $actions;
    }

    public function actionUpdate($id)
    {
        $model = $this->modelClass::findOne($id);

        if (empty($model)) {
            throw new NotFoundHttpException();
        }

        $model->setScenario('update');

        $model->load(Yii::$app->request->post(), '');
        $model->save();

        return $model;
    }

    /**
     * @OA\Get(
     *     path="/lessons/",
     *     tags={"lessons"},
     *     summary="List of lessons",
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
     *      @OA\Parameter(
     *         description="To return client extra data, for example, you can add payment to return client payment cards",
     *         in="query",
     *         name="expand",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),
     *      @OA\Parameter(
     *         description="From date (Y-m-d)",
     *         in="query",
     *         name="fromDate",
     *         required=false,
     *         type="string",
     *         items={ "type":"string" },
     *     ),
     *      @OA\Parameter(
     *         description="To date (Y-m-d)",
     *         in="query",
     *         name="toDate",
     *         required=false,
     *         type="string",
     *         items={ "type":"string" },
     *     ),
     *     @OA\Parameter(
     *         description="Search in subject, studentName, tutorName by keyword",
     *         in="query",
     *         name="keyword",
     *         required=false,
     *         type="string",
     *         items={ "type":"string" },
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="404", description="")
     * )
     */

    /**
     * @OA\Get(
     *     path="/lessons/{id}/",
     *     tags={"lessons"},
     *     summary="Get lesson data",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Lessons ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="To return client extra data, for example, you can add payment to return client payment cards",
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
     * @OA\Put(
     *     path="/lessons/{id}/",
     *     tags={"lessons"},
     *     summary="Update lesson",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Lessons ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="fromDate",
     *                 type="string",
     *                 description="From date Y-m-d H:i:s"
     *             ),
     *             @OA\Property(
     *                 property="toDate",
     *                 type="string",
     *                 description="To date Y-m-d H:i:s"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */

    /**
     * @OA\Get(
     *     path="/lessons/pdf/",
     *     tags={"lessons"},
     *     summary="Get lessons pdf",
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
     *         description="Search in subject, studentName, tutorName by keyword",
     *         in="query",
     *         name="keyword",
     *         required=false,
     *         type="string",
     *         items={ "type":"string" },
     *     ),
     *
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="404", description="")
     * )
     */
    public function actionPdf($fromDate = null, $toDate = null)
    {
        $limit = 600;
        $queryParams = Yii::$app->getRequest()->getQueryParams();
        $provider = (new LessonSearch());
        $provider->withRelatedModels = true;
        //if validation failed search() returns object of class LessonSearch
        $provider = $provider->disablePagination()->search($queryParams);

        if ($provider::className() === LessonSearch::className()) {
            return $provider;
        }

        if ($provider->getTotalCount() > $limit) {
            Yii::$app->response->setStatusCode(422);
            return [
                ['field' => '', 'message' => "Trying to download more than $limit lessons"]
            ];
        } else if ($provider->getTotalCount() === 0) {
            Yii::$app->response->setStatusCode(422);
            return [
                ['field' => '', 'message' => "Unable to download .pdf file. There are no lessons available"]
            ];
        }
        $accountModule = Yii::$app->getModule('account');
        $provider->query->orderBy('createdAt DESC');
        $content =  $this->renderPartial(
            'lessonsTable',
            [
                'lessonsQuery' => $provider->query,
                'companyName' => Yii::$app->user->getIdentity()->profile->companyName,
                'avatarPath' => $accountModule->getAvatarPath(),
            ]
        );

        $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_STRING,
            'content' => $content,
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            'cssInline' => '.kv-heading-1{font-size:18px}',
            'options' => ['title' => 'Lessons'],
        ]);

        return Yii::$app->response->sendContentAsFile(
            $pdf->render(),
            'Lessons.pdf',
            [
                'mimeType' => 'application/pdf',
                'inline' => true,
            ]
        );
    }

    /**
     * @OA\Get(
     *     path="/lessons/totals/",
     *     tags={"lessons"},
     *     summary="Get lessons totals",
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
     *         description="Search in subject, studentName, tutorName by keyword",
     *         in="query",
     *         name="keyword",
     *         required=false,
     *         type="string",
     *         items={ "type":"string" },
     *     ),
     *
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="404", description="")
     * )
     */
    public function actionTotals()
    {
        $queryParams = Yii::$app->getRequest()->getQueryParams();
        $provider = (new LessonSearch());
        //if validation failed search() returns object of class LessonSearch
        $provider = $provider->disablePagination()->search($queryParams);

        if ($provider instanceof LessonSearch) {
            return $provider;
        }

        /**
         * @var $query ActiveQuery
         */
        $query = $provider->query;

        $query->joinWith('clientBalanceTransaction');

        $query->addSelect([
            'duration' => new Expression('SUM(UNIX_TIMESTAMP(' . Lesson::tableName() . '.toDate) - UNIX_TIMESTAMP(' . Lesson::tableName() . '.fromDate)) / 3600'),
            'clientsCharged' => new Expression('SUM(ABS(' . ClientBalanceTransaction::tableName() . '.amount))'),
            'tutorsPaid' => new Expression('SUM(' . Lesson::tableName() . '.amount' . '+' . Lesson::tableName() . '.fee)'),
        ]);

        $result = $query->createCommand()->queryOne();

        foreach ($result as &$column) {
            $column = (float)$column;
        }
        return $result;
    }
}

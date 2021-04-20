<?php

namespace modules\account\controllers\api;

use api\components\rbac\Rbac;
use common\helpers\Role;
use modules\account\models\api\AccountClient;
use modules\account\models\api\AccountNote;
use modules\account\models\api\search\ClientNoteSearch;
use Yii;
use yii\rest\IndexAction;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class ClientNoteController extends \api\components\AuthController
{
    /**
     * @inheritdoc
     */
    public $modelClass = 'modules\account\models\api\AccountNote';

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
     * @OA\Get(
     *     path="/clients/{clientAccountId}/notes/",
     *     tags={"clients"},
     *     summary="List of client notes",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Client account ID",
     *         in="path",
     *         name="clientAccountId",
     *         required=true,
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
     *         description="Notes per page",
     *         in="query",
     *         name="per-page",
     *         required=false,
     *         type="integer"
     *     ),
     *      @OA\Parameter(
     *         description="Is pinned",
     *         in="query",
     *         name="isPinned",
     *         required=false,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="Extra fields: creator - to get account of user who create note, editor - get account user who made last update of note",
     *         in="query",
     *         name="expand",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),
     *     @OA\Response(response="200", description="")
     * )
     *
     * @OA\Post(
     *     path="/clients/{clientAccountId}/notes/",
     *     tags={"clients"},
     *     summary="Add client note",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Client account ID",
     *         in="path",
     *         name="clientAccountId",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="content",
     *                 type="string",
     *                 description="Note content (text)"
     *             ),
     *     @OA\Property(
     *                 property="isPinned",
     *                 type="integer",
     *                 description="Is Pinned"
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="")
     * )
     *
     * @OA\Put(
     *     path="/clients/{clientAccountId}/notes/{id}/",
     *     tags={"clients"},
     *     summary="Update client note",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Client account ID",
     *         in="path",
     *         name="clientAccountId",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="content",
     *                 type="string",
     *                 description="Note content (text)"
     *             ),
     *     @OA\Property(
     *                 property="isPinned",
     *                 type="integer",
     *                 description="Is Pinned"
     *             )
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Note ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     *
     * @OA\Delete(
     *     path="/clients/{clientAccountId}/notes/{id}/",
     *     tags={"clients"},
     *     summary="Remove note",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Client account ID",
     *         in="path",
     *         name="clientAccountId",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="Note ID",
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
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'prepareDataProvider' => function (IndexAction $action) {
                    $searchModel = new ClientNoteSearch();
                    return $searchModel->search(Yii::$app->getRequest()->getQueryParams());
                },
            ],
            'delete' => [
                'class' => 'yii\rest\DeleteAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }

    public function actionUpdate($id)
    {
        $model = AccountNote::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException();
        }

        $this->checkAccess($this->id);
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save()) {
            $model->refresh();
        }
        return $model;
    }


    public function actionCreate()
    {
        $this->checkAccess($this->id);

        /* @var $model \yii\db\ActiveRecord */
        $model = new $this->modelClass([
            'scenario' => $this->createScenario,
        ]);

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->accountId = Yii::$app->request->getQueryParam('clientAccountId');
        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        $model->refresh();
        return $model;
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        $clientAccountId = Yii::$app->request->getQueryParam('clientAccountId');
        if (
            $model
            && $clientAccountId != $model->accountId
        ) {
            throw new ForbiddenHttpException();
        }

        if (
            !is_numeric($clientAccountId)
            || !AccountClient::find()->andWhere(['id' => $clientAccountId])->exists()
        ) {
            throw new ForbiddenHttpException();
        }
    }
}

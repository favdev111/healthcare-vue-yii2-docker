<?php

namespace modules\account\controllers\api;

use api\components\AuthController;
use api\components\rbac\Rbac;
use modules\account\models\FileModel;
use modules\account\models\Job;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

/**
 * Class FilesController
 * @package modules\account\controllers\api
 */
class FilesController extends AuthController
{
    /**
     * @var string
     */
    public $modelClass = 'modules\account\models\Job';

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
     * @return array
     */
    public function actions(): array
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
            'view' => [
                'class' => 'modules\account\actions\DownloadAction',
            ]
        ];
    }

    /**
     * @OA\Post(
     *     path="/files/upload/",
     *     tags={"Files"},
     *     consumes={"multipart/form-data"},
     *     summary="Upload files",
     *     description="Upload files with next mime type (doc, docx, pptx, xls, xlsx, jpeg, png, pdf, txt)",
     *     security={{"Bearer":{}}},
     *     produces={"application/json"},
     *     @OA\Parameter(
     *         description="File to upload",
     *         in="formData",
     *         name="file",
     *         required=true,
     *         type="file"
     *     ),
     *     @OA\Parameter(
     *         description="Job ID",
     *         in="formData",
     *         name="jobId",
     *         required=false,
     *         type="integer"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="File successfully upload"
     *     ),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="405", description="Method Not Allowed"),
     *     @OA\Response(response="409", description="Can't upload file"),
     *     @OA\Response(response="422", description="Unprocessable Entity")
     * )
     */
    public function actionCreate()
    {
        try {
            $fileModel = new FileModel();
            $fileModel->file = UploadedFile::getInstanceByName('file');
            $id = Yii::$app->request->post('jobId');
            $job = Job::findOne($id);
            $fileModel->job_id = $job ? $job->id : null;

            if ($fileModel->validate('file')) {
                $fileName = time() . '_' . $fileModel->file->name;
                if (Yii::$app->fileSystem->put($fileName, fopen($fileModel->file->tempName, 'r+'))) {
                    $fileModel->file_name = $fileName;
                    $fileModel->size = $fileModel->file->size;
                    $fileModel->mime_type = $fileModel->file->type;
                    $fileModel->status = $job ? FileModel::STATUS_ATTACHED : FileModel::STATUS_DETACHED;
                    $fileModel->createdBy = Yii::$app->user->getId();
                    $fileModel->originalFileName = $fileModel->file->name;
                    $fileModel->save();
                    return $fileModel;
                }
                Yii::$app->response->statusCode = 409;
                return [
                    [
                        'field' => '',
                        'message' => 'Can\'t upload file',
                    ],
                ];
            }
            return $fileModel;
        } catch (\Exception $error) {
            Yii::error('Failed to upload file. Error: ' . $error->getMessage());
            Yii::$app->response->statusCode = 400;
            return [
                [
                    'field' => '',
                    'message' => 'Bad Request',
                ],
            ];
        }
    }

    /**
     * @OA\Delete(
     *     path="/files/delete/{id}/",
     *     tags={"Files"},
     *     summary="Delete File",
     *     description="Action for delete file",
     *     security={{"Bearer":{}}},
     *     produces={"application/json"},
     *     @OA\Parameter(
     *         description="File ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Response(response="204", description="No Content"),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="404", description="Not Found"),
     *     @OA\Response(response="405", description="Method Not Allowed")
     * )
     * @param int $id
     * @throws \Throwable
     */
    public function actionDelete(int $id)
    {
        $fileModel = FileModel::findOne($id);
        if (
            !$fileModel
            || !Yii::$app->fileSystem->has($fileModel->file_name)
        ) {
            throw new NotFoundHttpException();
        }

        if (
            !Yii::$app->fileSystem->delete($fileModel->file_name)
            || $fileModel->delete() === false
        ) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        Yii::$app->getResponse()->setStatusCode(204);
    }

    /**
     * @OA\Get(
     *     path="/files/download/{id}/",
     *     tags={"Files"},
     *     summary="Download file by id",
     *     description="Download file",
     *     security={{"Bearer":{}}},
     *     produces={"application/json"},
     *     @OA\Parameter(
     *         description="File ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="404", description="Not Found"),
     *     @OA\Response(response="405", description="Method Not Allowed")
     * )
     * @param int $id
     * @return array|\yii\console\Response|\yii\web\Response
     */
}

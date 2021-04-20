<?php

namespace modules\labels\controllers\api;

use api\components\rbac\Rbac;
use common\components\presenter\dto\LabelDTO;
use modules\labels\models\LabelRelationModel;
use modules\labels\models\Labels;
use modules\labels\models\LabelsCategory;
use modules\labels\models\LabelStatus;
use Yii;
use yii\data\ArrayDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

/**
 * Class LabelController
 * @package modules\labels\controllers\api
 */
class LabelController extends \api\components\AuthController
{
    /**
     * @var string
     */
    public $modelClass = 'modules\labels\models\Labels';

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
     *     path="/labels/categories/",
     *     tags={"Labels"},
     *     summary="List of labels category",
     *     description="Endpoint for get list of categories",
     *     security={{"Bearer":{}}},
     *     produces={"application/json"},
     *     @OA\Response(
     *         response=200,
     *         description="Operation success",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/LabelDTO")
     *         ),
     *     ),
     *     @OA\Response(response="404", description="Not Found"),
     *     @OA\Response(response="400", description="Bad request"),
     * )
     * @return array
     */
    public function actionCategories()
    {
        return LabelsCategory::find()->all();
    }

    /**
     * @OA\Get(
     *     path="/labels/{categorySlug}/",
     *     tags={"Labels"},
     *     summary="List of labels by category slug",
     *     description="Endpoint for get all list of labels by category slug",
     *     security={{"Bearer":{}}},
     *     produces={"application/json"},
     *     @OA\Parameter(
     *         description="Label Category slug",
     *         in="path",
     *         name="categorySlug",
     *         required=true,
     *         type="string"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Operation success",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/LabelDTO")
     *         ),
     *     ),
     *     @OA\Response(response="404", description="Not Found"),
     *     @OA\Response(response="400", description="Bad request"),
     * )
     * @param string $categorySlug
     * @return array|yii\data\ArrayDataProvider
     * @throws NotFoundHttpException
     */
    public function actionListByCategoryId(string $categorySlug)
    {
        $labelCategory = LabelsCategory::find()->andWhere(['slug' => $categorySlug])->one();
        if (!$labelCategory) {
            throw new NotFoundHttpException('Label Category not found');
        }
        $labels = Labels::find()
            ->andWhere([
                'categoryId' => $labelCategory->id,
                'status' => LabelStatus::createActiveStatus()->getStatus()
            ])
            ->all();
        if (!$labels) {
            throw new NotFoundHttpException('Labels not found');
        }

        $labelsList = [];
        foreach ($labels as $label) {
            $labelsList[] = (new LabelDTO(
                $label->id,
                $label->name,
                $label->color,
                $label->categoryId,
                null,
                null,
                null
            ))->toArray();
        }

        return new ArrayDataProvider([
            'allModels' => $labelsList,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
    }


    /**
     * @OA\Post(
     *     path="/labels/assign/",
     *     tags={"Labels"},
     *     summary="Create relation with label",
     *     description="",
     *     security={{"Bearer":{}}},
     *     produces={"application/json"},
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="labelId",
     *                 type="integer",
     *                 description="Label Id"
     *             ),
     *             @OA\Property(
     *                 property="itemId",
     *                 type="integer",
     *                 description="Related item Id"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="Description for other label"
     *             ),
     *         )
     *     ),
     *    @OA\Response(
     *          response="200",
     *          description="Operation success",
     *          ref="#/components/schemas/LabelDTO"
     *     ),
     *     @OA\Response(response="400", description="Bad request"),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     * @return array|LabelRelationModel
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionAssignToRelation()
    {
        $post = Yii::$app->request->post();

        $label = new LabelRelationModel();
        $label->labelId = $post['labelId'];
        $label->itemId = $post['itemId'];

        $labelModel = Labels::findOne($post['labelId']);

        if (strtolower($labelModel->name) === 'other') {
            $label->description = $post['description'] ?? null;
        }

        if (!$label->canAssign($post['itemId'])) {
            throw new ForbiddenHttpException('You are not allowed to perform this action');
        }

        $labelCategory = Labels::findOne($post['labelId']);

        if (!$labelCategory) {
            throw new BadRequestHttpException('Bad request');
        }

        if ($label->validate() && $label->save()) {
            return (new LabelDTO(
                $label->labelId,
                $label->label->name,
                $label->label->color,
                $label->label->categoryId,
                $label->description,
                $label->itemId,
                $label->id
            ))->toArray();
        }
        return $label;
    }

    /**
     * @OA\Put(
     *     path="/labels/assign/update/{relationId}/",
     *     tags={"Labels"},
     *     summary="Update relation label with new label id",
     *     description="",
     *     security={{"Bearer":{}}},
     *     produces={"application/json"},
     *     @OA\Parameter(
     *         description="Relation ID",
     *         in="path",
     *         name="relationId",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\RequestBody(
     *         @OA\Schema(
     *             type="object",
     *             @OA\Property(
     *                 property="labelId",
     *                 type="integer",
     *                 description="Label id"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Operation success",
     *          ref="#/components/schemas/LabelDTO"
     *     ),
     *     @OA\Response(response="400", description="Bad request"),
     *     @OA\Response(response="404", description="Relation Not Found"),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     * @param int $relationId
     * @return array|LabelRelationModel|null
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdateLabelRelation(int $relationId)
    {
        $label = LabelRelationModel::findOne($relationId);
        if (!$label) {
            throw new NotFoundHttpException('Labels not found');
        }

        if (!$label->canAssign($label->itemId)) {
            throw new ForbiddenHttpException('You are not allowed to perform this action');
        }

        $post = Yii::$app->request->post();

        $label->labelId = $post['labelId'];
        $labelModel = Labels::findOne($post['labelId']);

        if (strtolower($labelModel->name) === 'other') {
            $label->description = $post['description'] ?? null;
        }
        if ($label->validate() && $label->save()) {
            return (new LabelDTO(
                $label->labelId,
                $label->label->name,
                $label->label->color,
                $label->label->categoryId,
                $label->description,
                $label->itemId,
                $label->id
            ))->toArray();
        }

        return $label;
    }

    /**
     * @OA\Delete(
     *     path="/labels/delete/{relationId}/",
     *     tags={"Labels"},
     *     summary="Delete label relation",
     *     description="",
     *     security={{"Bearer":{}}},
     *     produces={"application/json"},
     *     @OA\Parameter(
     *         description="Label ID",
     *         in="path",
     *         name="relationId",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Response(response="204", description="No Content"),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="404", description="Relation Not Found")
     * )
     * @param int $relationId
     * @return array
     * @throws \Throwable
     */
    public function actionDeleteRelation(int $relationId): array
    {
        $label = LabelRelationModel::findOne($relationId);
        if (!$label) {
            throw new NotFoundHttpException('Relation Not Found');
        }

        if (!$label->canAssign($label->itemId)) {
            throw new ForbiddenHttpException('You are not allowed to perform this action');
        }

        if ($label->delete()) {
            Yii::$app->response->statusCode = 204;
            return [
                'field' => '',
                'message' => 'Operation success',
            ];
        }
        throw new UnprocessableEntityHttpException('Can\'t delete label relation');
    }
}

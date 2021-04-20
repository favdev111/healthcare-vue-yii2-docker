<?php

namespace modules\account\controllers\api;

use api\components\rbac\Rbac;
use common\helpers\Role;
use modules\account\models\api\search\ReviewSearch;
use modules\account\models\api\search\TutorSearch;
use Yii;
use yii\rest\IndexAction;

/**
 * Default controller for Review model
 */
class ReviewController extends \api\components\AuthController
{
    /**
     * @inheritdoc
     */
    public $modelClass = 'modules\account\models\api\tutor\Review';

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
            $searchModel = new ReviewSearch();
            return $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        };

        return $actions;
    }

    /**
     * @OA\Get(
     *     path="/reviews/",
     *     tags={"reviews"},
     *     summary="List of reviews",
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
     *         description="Reviews per page",
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
     *         description="Tutor ID",
     *         in="query",
     *         name="accountId",
     *         required=false,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="To return review extra data",
     *         in="query",
     *         name="expand",
     *         required=false,
     *         type="array",
     *         items={ "type":"string" },
     *     ),
     *     @OA\Response(response="200", description="")
     * )
     */
}

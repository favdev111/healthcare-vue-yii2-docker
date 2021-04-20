<?php

namespace modules\account\controllers\api;

use api\components\rbac\Rbac;
use common\helpers\Role;
use modules\account\models\JobOffer;
use yii\web\ForbiddenHttpException;

/**
 * Default controller for Job Offer model
 */
class JobOfferController extends \api\components\AuthController
{
    public function init()
    {
        parent::init();
        $this->updateScenario = JobOffer::SCENARIO_UPDATE;
    }

    /**
     * @inheritdoc
     */
    public $modelClass = 'modules\account\models\api\JobOffer';

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
        return $actions;
    }

    /**
     * @OA\Post(
     *     path="/job-offers/",
     *     tags={"job-offers"},
     *     summary="Add new job offer",
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
     *                 property="amount",
     *                 type="integer",
     *                 description="Job Offer Amount"
     *             ),
     *              @OA\Property(
     *                 property="shareContactInfo",
     *                 type="integer",
     *                 description="Share client contact info in message to tutor",
     *                 enum={"1", "2"}
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
     *     path="/job-offers/{id}/",
     *     tags={"job-offers"},
     *     summary="Update job offer status",
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
     *                 property="status",
     *                 type="integer",
     *                 description="Job Offer Status (1 - Confirmed, 2 - Declined)",
     *                 enum={"1", "2"}
     *             ),
     *              @OA\Property(
     *                 property="shareContactInfo",
     *                 type="integer",
     *                 description="Share client contact info in message to tutor",
     *                 enum={"1", "2"}
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */

    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action === 'update') {
            /**
             * @var $model JobOffer
             */
            if ($model->status !== JobOffer::STATUS_PENDING || $model->jobHasHire()) {
                throw new ForbiddenHttpException('You can not accept or decline offers for jobs that have accepted or declined hires.');
            }
        }
    }
}

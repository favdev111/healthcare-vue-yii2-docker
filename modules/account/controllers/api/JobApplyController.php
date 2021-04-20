<?php

namespace modules\account\controllers\api;

use api\components\rbac\Rbac;
use common\helpers\Role;

/**
 * Default controller for Job Apply model
 */
class JobApplyController extends \api\components\AuthController
{
    /**
     * @inheritdoc
     */
    public $modelClass = 'modules\account\models\api\JobApply';

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
        unset($actions['index']);
        unset($actions['update']);
        unset($actions['delete']);

        return $actions;
    }

    /**
     * @OA\Post(
     *     path="/job-apply/",
     *     tags={"job-apply"},
     *     summary="Create new job apply",
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
     *                 property="accountId",
     *                 type="integer",
     *                 description="Tutor ID"
     *             ),
     *             @OA\Property(
     *                 property="isOnlineBefore",
     *                 type="integer",
     *                 description="answer for Have you tutored online before? question"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="Strign from applicant"
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="201", description="Job Hire created"),
     *     @OA\Response(response="200", description=""),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
}

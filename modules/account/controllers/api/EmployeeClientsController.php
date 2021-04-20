<?php

namespace modules\account\controllers\api;

use modules\account\models\Role;

class EmployeeClientsController extends \api\components\AuthController
{
    public $modelClass = 'modules\account\models\api\EmployeeClient';

    public function behaviorAccess()
    {
        return [
            [
                'allow' => true,
                'roles' => [Role::ROLE_CRM_ADMIN],
            ],
        ];
    }

    /**
     * @OA\Delete(
     *     path="/employee-clients/{id}/",
     *     tags={"employees-clients"},
     *     summary="Remove employee-client relation",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Employee-client relation ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Response(response="204", description=""),
     *     @OA\Response(response="404", description="")
     * )
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['index']);
        return $actions;
    }
}

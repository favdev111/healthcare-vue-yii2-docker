<?php

namespace modules\account\controllers\console;

use api\components\rbac\Rbac as ApiRbac;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionGivePermissionManageUsers($companyId)
    {
        ApiRbac::givePermissionManageUsers($companyId);
    }

    /**
     * Re-create all rbac permission
     * @param $companyId - id of company which can manage employees
     */
    public function actionReInitAllRules($companyId)
    {
        \api\components\rbac\Rbac::clearTables();
        \backend\components\rbac\Rbac::initialization();
        \api\components\rbac\Rbac::initialization();
        ApiRbac::givePermissionManageUsers($companyId);
        ApiRbac::createTeamRoles();
    }
}

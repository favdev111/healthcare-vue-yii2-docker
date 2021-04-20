<?php

use api\components\rbac\Rbac as ApiRbac;
use yii\db\Migration;

/**
 * Class m200915_071837_reset_rbac_permissions
 */
class m200915_071837_reset_rbac_permissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \api\components\rbac\Rbac::clearTables();
        \backend\components\rbac\Rbac::initialization();
        \api\components\rbac\Rbac::initialization();
        $query = \modules\account\models\ar\Account::find()->crmAdmin();
        foreach ($query->each() as $crmAdminAccount) {
            ApiRbac::givePermissionManageUsers($crmAdminAccount->id);
        }
        ApiRbac::createTeamRoles();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}

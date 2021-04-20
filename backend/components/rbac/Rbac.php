<?php

namespace backend\components\rbac;

use backend\models\Account as BackendAccount;
use modules\account\models\Role;
use Yii;
use yii\base\BaseObject;

/**
 * Class Rbac
 * @package backend\components\rbac
 */
class Rbac extends BaseObject
{
    const PERMISSION_ADMINS_MANAGEMENT = 'admins-management';
    const PERMISSION_SEO_MANAGEMENT = 'seo-management';
    const PERMISSION_BACKEND_FULL_MANAGEMENT = 'backend-full-management';
    const PERMISSION_VIEW_ALL = 'backend-view-all';
    const PERMISSION_VIEW_TRANSACTIONS = 'backend-view-transactions';

    //pasted from migration m170915_084226_rbac_data
    const SUPER_ADMIN_ID = 1;
    const PRODUCTION_SUPER_ADMIN = 'admin@admin.com';

    public static function assignRoles()
    {
        /**
         * @var BackendAccount $superAdmin
         */
        $superAdmin = BackendAccount::find()->andWhere(['email' => self::PRODUCTION_SUPER_ADMIN])->one();

        if (!$superAdmin) {
            $superAdmin = BackendAccount::find()->andWhere(['id' => self::SUPER_ADMIN_ID])->one();
        }

        if ($superAdmin) {
            $superAdmin->roleId = Role::ROLE_SUPER_ADMIN;
            $superAdmin->save();
        }

        $admins = BackendAccount::find()->all();
        foreach ($admins as $admin) {
            $admin->save();
        }
    }

    /**
     * Create initial initialization
     */
    public static function initialization()
    {
        $auth = Yii::$app->authManager;

        /**
         * Add roles
         */
        $superAdmin = $auth->createRole(Role::ROLE_SUPER_ADMIN);
        $superAdmin->description = 'Super admin role';
        $auth->add($superAdmin);

        $admin = $auth->createRole(Role::ROLE_ADMIN);
        $admin->description = 'Admin role';
        $auth->add($admin);

        $seoAdmin = $auth->createRole(Role::ROLE_SEO);
        $seoAdmin->description = 'Seo admin role';
        $auth->add($seoAdmin);

        $testerAdmin = $auth->createRole(Role::ROLE_TESTER_ADMIN);
        $testerAdmin->description = 'Tester admin role';
        $auth->add($testerAdmin);

        /**
         * Add permissions
         */
        $seoManagement = $auth->createPermission(self::PERMISSION_SEO_MANAGEMENT);
        $seoManagement->description = 'Full backend management except admin management';
        $auth->add($seoManagement);

        $backendFullManagement = $auth->createPermission(self::PERMISSION_BACKEND_FULL_MANAGEMENT);
        $backendFullManagement->description = 'Full backend management except admin management';
        $auth->add($backendFullManagement);

        $viewAllPermission = $auth->createPermission(self::PERMISSION_VIEW_ALL);
        $viewAllPermission->description = 'View all backend data';
        $auth->add($viewAllPermission);

        $viewTransactions = $auth->createPermission(self::PERMISSION_VIEW_TRANSACTIONS);
        $viewTransactions->description = 'View all transactions amount';
        $auth->add($viewTransactions);

        $auth->addChild($backendFullManagement, $seoManagement);
        $auth->addChild($backendFullManagement, $viewAllPermission);
        $auth->addChild($backendFullManagement, $viewTransactions);

        $auth->addChild($admin, $backendFullManagement);
        $auth->addChild($superAdmin, $backendFullManagement);
        $auth->addChild($testerAdmin, $viewAllPermission);
        $auth->addChild($seoAdmin, $seoManagement);

        static::assignRoles();
    }
}

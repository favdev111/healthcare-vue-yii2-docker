<?php

namespace api\components\rbac;

use modules\account\models\Account;
use modules\account\models\Role;
use modules\account\models\Team;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Console;

class Rbac extends BaseObject
{
    //use actions that can change balance of user
    const PERMISSION_CAN_CHANGE_BALANCE = 'change-balances-permission';
    //access to users tab (manage employee, send invitation)
    const PERMISSION_CAN_MANAGE_EMPLOYEES = 'manage-employees-permission';
    //base permission that include access for biggest part of functionality (except actions that change balance and employees)
    const PERMISSION_BASE_B2B_PERMISSIONS = 'base-b2b-permissions';

    const PERMISSION_CAN_ACCESS_NOTIFICATION = 'notification-access-permission';
    const PERMISSION_CAN_CREATE_CLIENTS = 'can-create-clients-permission';

    public static function initialization()
    {
        Console::output('Creating roles and permissions');
        $auth = Yii::$app->authManager;

        //roles
        $companyOwnerRole = $auth->createRole(Role::ROLE_CRM_ADMIN);
        $companyOwnerRole->description = 'Company owner role';
        $auth->add($companyOwnerRole);

        $companyEmployeeRole = $auth->createRole(Role::ROLE_COMPANY_EMPLOYEE);
        $companyEmployeeRole->description = 'Company Employee role';
        $auth->add($companyEmployeeRole);

        //permissions
        $base = $auth->createPermission(self::PERMISSION_BASE_B2B_PERMISSIONS);
        $base->description = 'Access for main part of platform functionality';
        $auth->add($base);

        //change balance
        $changeBalance = $auth->createPermission(self::PERMISSION_CAN_CHANGE_BALANCE);
        $changeBalance->description = 'Change balance: manual charges, refunds, recharges.';
        $auth->add($changeBalance);

        //manage employees
        $manageEmployees = $auth->createPermission(self::PERMISSION_CAN_MANAGE_EMPLOYEES);
        $manageEmployees->description = 'Create/block/delete employees and admins';
        $auth->add($manageEmployees);

        $auth->addChild($companyEmployeeRole, $base);
        $auth->addChild($companyOwnerRole, $companyEmployeeRole);
        $auth->addChild($companyOwnerRole, $changeBalance);

        Console::output('Assing company owners');
        $companyIds = Account::findWithoutRestrictions()
            ->andWhere(['roleId' => Role::ROLE_CRM_ADMIN])
            ->select('id')->column();
        foreach ($companyIds as $companyId) {
            $auth->assign($companyOwnerRole, $companyId);
        }

        Console::output('Assing company employees');
        $employeesIds = Account::findWithoutRestrictions()
            ->andWhere(['roleId' => Role::ROLE_COMPANY_EMPLOYEE])
            ->select('id')->column();
        foreach ($employeesIds as $employeeId) {
            $auth->assign($companyEmployeeRole, $employeeId);
        }
    }

    public static function givePermissionManageUsers($companyId)
    {
        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission(static::PERMISSION_CAN_MANAGE_EMPLOYEES);
        //assign to company
        $auth->assign($permission, $companyId);
    }

    public static function createTeamRoles()
    {
        $auth = \Yii::$app->authManager;
        $teamRoles = Team::getList();
        $basePermission = $auth->getPermission(Rbac::PERMISSION_BASE_B2B_PERMISSIONS);

        foreach ($teamRoles as $teamRole) {
            $teamRoleName = Team::getTeamRoleName($teamRole);
            $role = $auth->createRole($teamRoleName);
            $role->description = $teamRole;
            $auth->add($role);
            $auth->addChild($role, $basePermission);
        }

        //create new permission
        $notificationApiPermission = $auth->createPermission(Rbac::PERMISSION_CAN_ACCESS_NOTIFICATION);
        $notificationApiPermission->description = 'Access for notification API section';
        $auth->add($notificationApiPermission);


        //assign new permission for OPS team
        $opsTeamRoleName = Team::getTeamRoleName(Team::OPS_TEAM_LABEL);
        $roleOps = $auth->getRole($opsTeamRoleName);
        $auth->addChild($roleOps, $notificationApiPermission);

        //update current employee role for employees. Replace it to OPS team role.
        Yii::$app->db
            ->createCommand()
            ->update('{{%auth_assignment}}', ['item_name' => $opsTeamRoleName], ['item_name' => (string)Role::ROLE_COMPANY_EMPLOYEE])
            ->execute();


        //create client permission
        $createClientPermission = $auth->createPermission(Rbac::PERMISSION_CAN_CREATE_CLIENTS);
        $createClientPermission->description = 'Possibility to create new clients';
        $auth->add($createClientPermission);

        //assign create client permission to Sales team and admins
        $salesReamRoleName = Team::getTeamRoleName(Team::SALES_TEAM_LABEL);
        $roleSales = $auth->getRole($salesReamRoleName);
        $auth->addChild($roleSales, $createClientPermission);

        //assign this permission to admin and owner
        $companyOwnerRole = $auth->getRole(Role::ROLE_CRM_ADMIN);
        $auth->addChild($companyOwnerRole, $createClientPermission);
    }

    public static function clearTables()
    {
        $prefix = \Yii::$app->db->tablePrefix . 'auth_';
        $rbacTables = [];
        $rbacTables[] = $prefix . 'item';
        $rbacTables[] = $prefix . 'item_child';
        $rbacTables[] = $prefix . 'assignment';
        $rbacTables[] = $prefix . 'rule';

        foreach ($rbacTables as $rbacTable) {
            \Yii::$app->db->createCommand()->delete($rbacTable)->execute();
        }
    }
}

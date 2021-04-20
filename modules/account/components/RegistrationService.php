<?php

namespace modules\account\components;

use api\components\rbac\Rbac;
use modules\account\models\ar\Account as AccountAR;
use modules\account\models\Role;
use yii\base\Component;
use yii\di\Instance;
use yii\rbac\DbManager;

/**
 * Class Registration
 * @package modules\account\components
 */
class RegistrationService extends Component
{
    public $authManager = 'authManager';

    public function init()
    {
        parent::init();
        $this->authManager = Instance::ensure($this->authManager, DbManager::class);
    }

    /**
     * @param array $accountData
     * @return AccountAR
     * @throws \yii\base\Exception
     */
    protected function createAccount(array $accountData): AccountAR
    {
        $account = new AccountAR();
        if (!empty($accountData['password'])) {
            $accountData['passwordHash'] = \Yii::$app->security->generatePasswordHash($accountData['password']);
        }
        $account->publicId = \Yii::$app->security->generateRandomString();
        $account->load($accountData, '');
        $account->save(false);
        return $account;
    }

    /**
     * @param $account
     * @param int $roleId
     * @throws \Exception
     */
    protected function assignRole($account, int $roleId)
    {
        $role = $this->authManager->getRole($roleId);
        $this->authManager->assign($role, $account->id);
    }

    /**
     * @param string $email
     * @param string $password
     * @throws \yii\base\Exception
     */
    public function createCrmAdmin(string $email, string $password)
    {
        $account = $this->createAccount(
            [
                'email' => $email,
                'password' => $password,
                'roleId' => Role::ROLE_CRM_ADMIN
            ]
        );
        $account->update(['status' => AccountAR::STATUS_ACTIVE]);
        $this->assignRole($account, Role::ROLE_CRM_ADMIN);
        Rbac::givePermissionManageUsers($account->id);
    }
}

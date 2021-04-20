<?php

namespace backend\components;

use common\components\ZipCodeHelper;
use modules\account\models\backend\Job;
use Yii;

/**
 * User component
 */
class Account extends \yii\web\User
{

    /**
     * Check if user can do $permissionName.
     * If "authManager" component is set, this will simply use the default functionality.
     * Otherwise, it will use our custom permission system
     * @param string $permissionName
     * @param array $params
     * @param bool $allowCaching
     * @return bool
     */
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        // check for auth manager to call parent
        $auth = Yii::$app->getAuthManager();
        if ($auth) {
            return parent::can($permissionName, $params, $allowCaching);
        }

        // otherwise use our own custom permission (via the role table)
        $account = $this->getIdentity();
        return $account ? $account->can($permissionName) : false;
    }
}

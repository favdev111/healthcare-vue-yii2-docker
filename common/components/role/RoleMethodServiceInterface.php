<?php

namespace common\components\role;

use yii\base\InvalidConfigException;

interface RoleMethodServiceInterface
{
    /**
     * get method name for identity user
     * @param $method
     * @return string
     */
    public function getIdentityUserMethod($method);

    /**
     * Find specific method name for role
     * @param $method
     * @param $roleName
     * @return string
     * @throws InvalidConfigException
     * @throws MethodNotExistException
     */
    public function getMethodByRoleName($method, $roleName);
}

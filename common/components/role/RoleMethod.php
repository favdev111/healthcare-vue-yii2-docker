<?php

namespace common\components\role;

use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\BaseObject;
use yii\di\NotInstantiableException;
use yii\di\ServiceLocator;
use Yii;

/**
 * Class RoleMethod
 */
class RoleMethod extends ServiceLocator implements RoleMethodServiceInterface
{
    /**
     * @param array $roles
     */
    public function setRoles($roles)
    {
        parent::setComponents($roles);
    }

    /**
     * Returns the list of the component definitions or the loaded component instances.
     * @param bool $returnDefinitions whether to return component definitions instead of the loaded component instances.
     * @return array the list of the component definitions or the loaded component instances (ID => definition or instance).
     */
    public function getRoles($returnDefinitions = true): array
    {
        return parent::getComponents($returnDefinitions);
    }

    /**
     * @inheritdoc
     */
    public function getMethodByRoleName($method, $roleName)
    {
        /**
         * @var RoleMethodInterface $roleClassObject
         */
        $roleClassObject = $this->{$roleName};

        if (!$roleClassObject) {
            throw new InvalidConfigException("Method class is not configured for role: \"$roleName\"");
        }

        $methods = $roleClassObject->methods();

        if (!isset($methods[$method])) {
            throw new MethodNotExistException("\"$method\" method is not found for role \"$roleName\"");
        }

        return $methods[$method];
    }

    /**
     * @inheritdoc
     */
    public function getIdentityUserMethod($method)
    {
        $user = Yii::$app->user;
        if ($user->isGuest) {
            throw new InvalidCallException('Method is allowed only for identity user');
        }

        return $this->getMethodByRoleName($method, $user->identity->getRoleName());
    }

    /**
     * Getter magic method.
     * This method is overridden to support accessing components like reading properties.
     * @param string $name component or property name
     * @throws NotInstantiableException
     * @return mixed the named property value or null
     */
    public function __get($name)
    {
        if ($this->has($name)) {
            $dependency = $this->get($name);
            if ($dependency instanceof RoleMethodInterface === false) {
                throw new NotInstantiableException("\"$name\" should be instance of " . RoleMethodInterface::class);
            }
            return $dependency;
        }
    }
}

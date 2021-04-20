<?php

namespace common\components\role;

/**
 * Interface RoleMethodInterface
 * @package common\components\role
 */
interface RoleMethodInterface
{
    const WELCOME_NOTIFICATION = 'getWelcomeNotification';

    /**
     * Methods list specific for role
     * @return array
     */
    public function methods(): array;
}

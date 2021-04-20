<?php

namespace common\components\role;

use Imagine\Exception\InvalidArgumentException;

/**
 * Class TutorMethod
 * @package common\components\role
 */
class TutorMethod implements RoleMethodInterface
{
    /**
     * Methods list specific for role
     * @return array
     */
    public function methods(): array
    {
        return [
            static::WELCOME_NOTIFICATION => 'getTutorConfirmedEmail',
        ];
    }
}

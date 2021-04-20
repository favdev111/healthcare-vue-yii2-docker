<?php

namespace common\components\role;

use Imagine\Exception\InvalidArgumentException;

/**
 * Class StudentMethod
 * @package common\components\role
 */
class StudentMethod implements RoleMethodInterface
{
    /**
     * Find specific method name for role
     * @return array
     */
    public function methods(): array
    {
        return [
            static::WELCOME_NOTIFICATION => 'getStudentFirstSearch',
        ];
    }
}

<?php

namespace modules\account\responses;

use common\components\Response;
use modules\account\models\api2Patient\entities\healthProfile\HealthProfile as APIPatientHealthProfile;

/**
 * Class HealthProfile
 * @package modules\account\responses
 *
 * @property APIPatientHealthProfile $resource
 */
class HealthProfileShort extends Response
{
    public function fields()
    {
        return [
            'id',
            'isMain',
            'firstName',
            'lastName',
        ];
    }
}

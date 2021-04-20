<?php

namespace modules\account\models\forms\professional\role;

use api2\helpers\DoctorType;
use api2\helpers\ProfessionalType;
use backend\models\Option;
use modules\account\models\ar\State;

/**
 * Class ProfessionalRoleOption
 * @package modules\account\models\forms\professional\role
 *
 * @property-read array $doctorTypes
 * @property-read array $states
 * @property-read array $professionalTypes
 */
class ProfessionalRoleOption extends Option
{
    /**
     * @return array
     * @throws \Exception
     */
    public function getStates(): array
    {
        return $this->getOptions('states', State::find(), 'name');
    }

    /**
     * @return array
     */
    public function getDoctorTypes(): array
    {
        return DoctorType::DOCTORS_SPECIALIZATION_LABELS;
    }

    /**
     * @return array
     */
    public function getProfessionalTypes(): array
    {
        return ProfessionalType::LABELS;
    }
}

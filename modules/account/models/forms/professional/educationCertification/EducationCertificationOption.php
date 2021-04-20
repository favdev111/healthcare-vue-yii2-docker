<?php

namespace modules\account\models\forms\professional\educationCertification;

use backend\models\Option;
use modules\account\models\EducationCollege;
use modules\account\models\EducationDegree;

/**
 * Class EducationCertificationOption
 * @package modules\account\models\forms\professional\educationCertification
 *
 * @property-read array $educationDegree
 * @property-read array $educationCollege
 */
class EducationCertificationOption extends Option
{
    /**
     * @return array
     * @throws \Exception
     */
    public function getEducationCollege(): array
    {
        return $this->getOptions('educationCollege', EducationCollege::find(), 'name');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getEducationDegree(): array
    {
        return $this->getOptions('educationDegree', EducationDegree::find(), 'name');
    }
}

<?php

namespace modules\account\models\forms\professional\specifications;

use backend\models\Option;
use common\models\health\AutoimmuneDisease;
use common\models\health\HealthGoal;
use common\models\health\HealthTest;
use common\models\health\MedicalCondition;
use common\models\health\Symptom;

/**
 * Class ProfessionalSpecificationsOption
 * @package modules\account\models\forms\professional\specifications
 *
 * @property-read array $symptoms
 * @property-read array $medicalConditions
 * @property-read array $healthGoals
 * @property-read array $healthTests
 * @property-read array $autoimmuneDiseases
 */
class ProfessionalSpecificationsOption extends Option
{
    /**
     * @return array
     * @throws \Exception
     */
    public function getAutoimmuneDiseases(): array
    {
        return $this->getOptions('autoimmuneDiseases', AutoimmuneDisease::find(), 'name');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getHealthGoals(): array
    {
        return $this->getOptions('healthGoals', HealthGoal::find(), 'name');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getMedicalConditions(): array
    {
        return $this->getOptions('medicalConditions', MedicalCondition::find(), 'name');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getHealthTests(): array
    {
        return $this->getOptions('healthTests', HealthTest::find(), 'name');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getSymptoms(): array
    {
        return $this->getOptions('symptoms', Symptom::find(), 'name');
    }
}

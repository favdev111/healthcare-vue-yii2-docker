<?php

namespace modules\account\models\forms\patient;

use modules\account\models\Account;
use modules\account\models\forms\UpdateForm;

/**
 * Class ProfessionalUpdateForm
 *
 * @property-read array $statuses
 * @property-read Account $account
 */
class PatientUpdateForm extends UpdateForm
{
    /**
     * @return string[][]
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => ['firstName', 'lastName', 'statusId', 'zipCode', 'phoneNumber'],
        ];
    }
}

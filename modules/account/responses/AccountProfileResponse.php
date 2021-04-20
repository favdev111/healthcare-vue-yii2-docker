<?php

namespace modules\account\responses;

use common\components\Response;
use modules\account\models\Profile;

/**
 * Class ProfileResponse
 * @package modules\account\responses
 *
 * @property Profile $resource
 */
class AccountProfileResponse extends Response
{
    public function fields()
    {
        $data = [
            'firstName',
            'lastName',
            'showName',
            'phoneNumber',
        ];
        $fields = [];
        if ($this->resource->account->isSpecialist()) {
            $fields = $this->specialist();
        } elseif ($this->resource->account->isPatient()) {
            $fields = $this->patient();
        }

        return array_merge($data, $fields);
    }

    protected function specialist()
    {
        return [
            'gender',
            'address',
            'zipCode',
            'googlePlaceId',
            'professionalTypeId',
            'doctorTypeId',
            'npiNumber',
            'yearsOfExperience',
            'isBoardCertified',
            'hasDisciplinaryAction',
            'disciplinaryActionText',
            'currentlyEnrolled',
            'dateOfBirth',
            'title',
            'description',
        ];
    }

    protected function patient()
    {
        return [];
    }
}

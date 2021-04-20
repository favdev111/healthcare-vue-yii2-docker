<?php

namespace modules\account\responses;

use common\components\Response;
use modules\account\models\Account;

/**
 * Class AccountResponse
 * @package modules\account\responses
 *
 * @property Account $resource
 */
class AccountResponse extends Response
{
    public function fields()
    {
        $data = [];
        if ($this->resource->isSpecialist()) {
            $data = $this->specialist();
        } elseif ($this->resource->isPatient()) {
            $data = $this->patient();
        }

        if (!empty($this->resource->accessToken)) {
            $data[] = 'accessToken';
        }

        $data['thumbnails'] = function () {
            $thumbnails = [];
            $types = array_keys($this->resource->getThumbnailTypes());
            foreach ($types as $type) {
                $thumbnails[$type] = $this->resource->getThumbnailUrl($type);
            }

            return $thumbnails;
        };

        return $data;
    }

    protected function specialist()
    {
        return [
            'id',
            'email',
            'roleId',
            'profile' => function () {
                return new AccountProfileResponse($this->profile);
            },
            'rate',
            'telehealthStates',
            'licenceStates',
            'educations',
            'certifications',
            'healthGoals',
            'autoimmuneDiseases',
            'symptoms',
            'healthTests',
            'languages',
            'insuranceCompanies',
            'medicalConditions',
            'placeId' => function () {
                return $this->profile->googlePlace->placeId ?? '';
            },
            'registrationStep',
        ];
    }

    protected function patient()
    {
        return [
            'id',
            'email',
            'roleId',
            'profile' => function () {
                return new AccountProfileResponse($this->profile);
            },
            'healthProfiles' => function () {
                $data = [];

                foreach ($this->resource->healthProfiles as $healthProfile) {
                    $data[] = new HealthProfileShort($healthProfile);
                }

                return $data;
            },
        ];
    }
}

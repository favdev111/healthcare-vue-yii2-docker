<?php

namespace modules\account\responses;

use common\components\Response;
use modules\account\models\api2Patient\entities\healthProfile\health\medical\HealthProfileHealthMedical;
use modules\account\models\api2Patient\entities\healthProfile\HealthProfile as APIPatientHealthProfile;
use yii\helpers\ArrayHelper;

/**
 * Class HealthProfile
 * @package modules\account\responses
 *
 * @property APIPatientHealthProfile $resource
 */
class HealthProfile extends Response
{
    public function fields()
    {
        $fields = [
            'id',
            'isMain',
            'firstName',
            'lastName',
            'birthday' => function () {
                return $this->resource->birthday ? $this->resource->birthday->format('m/d/Y') : null;
            },
            'gender',
            'height',
            'weight',
            'zipcode',
            'address',
            'country',
            'googlePlaceId',
            'health' => 'healthProfileHealth',
        ];

        $fields = array_merge(
            $fields,
            $this->resource->isMain ? $this->mainHealthProfile() : $this->notMainHealthProfile()
        );

        if ($this->resource->healthProfileHealthMedicals) {
            $fields['medical'] = function () {
                $healthMedicals = $this->resource->healthProfileHealthMedicals;

                return ArrayHelper::index(
                    $healthMedicals,
                    null,
                    function (HealthProfileHealthMedical $healthProfileHealthMedical) {
                        return $healthProfileHealthMedical->type->stringId;
                    }
                );
            };
        }

        return $fields;
    }

    protected function mainHealthProfile(): array
    {
        return [
            'phoneNumber',
            'email',
            'maritalStatusId',
            'childrenCount',
            'educationLevelId',
            'occupation',
            'employer',
            'insurances',
        ];
    }

    protected function notMainHealthProfile(): array
    {
        return [
            'relationshipId',
        ];
    }
}

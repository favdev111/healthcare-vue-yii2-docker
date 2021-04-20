<?php

namespace modules\account\models\api2\forms;

use api2\helpers\DoctorType;
use api2\helpers\EnrolledTypes;
use api2\helpers\ProfessionalType;
use common\components\validators\LicenseValidator;
use common\components\validators\NPIValidator;
use modules\account\models\api2\InsuranceCompany;
use modules\account\models\api2\State;
use Yii;
use yii\base\Model;

final class RegistrationWizardStep2 extends Model
{
    public $professionalTypeId;
    public $doctorTypeId;
    public $npiNumber;
    public $yearsOfExperience;
    public $isBoardCertified;
    public $hasDisciplinaryAction;
    public $disciplinaryActionText;
    public $currentlyEnrolled;
    public $insuranceCompanies;

    public $licenceStates = [];
    public $telehealthStates = [];

    public function rules()
    {
        return [
            //type
            [
                [
                    'doctorTypeId',
                    'professionalTypeId',
                    'isBoardCertified',
                    'hasDisciplinaryAction',
                    'currentlyEnrolled',
                ],
                'integer'
            ],
            [
                [
                    'disciplinaryActionText',
                ],
                'string'
            ],
            //required
            [
                [
                    'professionalTypeId',
                    'yearsOfExperience',
                    'licenceStates',
                    'telehealthStates',
                    'hasDisciplinaryAction',
                ],
                'required'
            ],
            [
                'disciplinaryActionText',
                'required',
                'when' => function () {
                    return (bool)$this->hasDisciplinaryAction;
                }
            ],
            [
                ['currentlyEnrolled'],
                'required',
                'when' => function () {
                    return in_array(
                        (int)$this->professionalTypeId,
                        [
                            ProfessionalType::DOCTOR,
                            ProfessionalType::NURSE_PRACTITIONER,
                        ]
                    );
                }
            ],
            [
                ['isBoardCertified'],
                'required',
                'when' => function () {
                    return ProfessionalType::DOCTOR == $this->professionalTypeId;
                }
            ],
            [
                ['doctorTypeId'],
                'required',
                'when' => function () {
                    return in_array(
                        $this->professionalTypeId,
                        [
                            ProfessionalType::DOCTOR,
                        ]
                    );
                }
            ],
            //specific
            //professionalTypeId
            [
                'professionalTypeId',
                'in',
                'range' => ProfessionalType::getAllTypes()
            ],
            //doctorTypeId
            [
                'doctorTypeId',
                'in',
                'range' => DoctorType::getDoctorTypes(),
                'when' => function () {
                    return in_array(
                        $this->professionalTypeId,
                        [
                            ProfessionalType::DOCTOR,
                        ]
                    );
                }
            ],
            [
                'doctorTypeId',
                'in',
                'range' => DoctorType::getNurseTypes(),
                'when' => function () {
                    return in_array(
                        $this->professionalTypeId,
                        [
                            ProfessionalType::NURSE_PRACTITIONER,
                        ]
                    );
                }
            ],
            [
                'currentlyEnrolled',
                'in',
                'range' => EnrolledTypes::getAllTypes(),
                'when' => function () {
                    return in_array(
                        $this->professionalTypeId,
                        [
                            ProfessionalType::DOCTOR,
                            ProfessionalType::NURSE_PRACTITIONER,
                        ]
                    );
                },
            ],
            //npiNumber
            [
                [
                    'npiNumber',

                ],
                'string',
                'length' => 10
            ],
            [
                'npiNumber',
                'required',
                'when' => function () {
                    return $this->professionalTypeId == ProfessionalType::DOCTOR;
                }
            ],
            [
                'npiNumber',
                NPIValidator::class,
                'when' => function () {
                    return $this->professionalTypeId == ProfessionalType::DOCTOR;
                }
            ],
            //yearsOfExperience
            [
                'yearsOfExperience',
                'integer',
                'min' => 0,
                'max' => 99,
            ],
            //insuranceCompanies
            [
                'insuranceCompanies',
                'insuranceCompanyValidator'
            ],
            [
                ['licenceStates', 'telehealthStates'],
                'stateIdValidator'
            ],
            [
                ['licenceStates'],
                'stateLicenceValidator'
            ],
        ];
    }

    public function insuranceCompanyValidator($attribute, $params, $validator)
    {
        $attributeValue = $this->$attribute;

        foreach ($attributeValue as $item) {
            $id = $item['insuranceCompanyId'] ?? null;
            if (empty($id)) {
                $this->addError($attribute, 'InsuranceCompanyId is required');
            }
            $isExists = InsuranceCompany::find()->andWhere(['id' => $id])->exists();
            if (!$isExists) {
                $this->addError($attribute, 'Incorrect insurance company provided');
            }
        }
    }

    public function stateLicenceValidator($attribute, $params, $validator)
    {
        $attributeValue = $this->$attribute;
        $licenseValidator = Yii::createObject(LicenseValidator::class);
        foreach ($attributeValue as $item) {
            $licence = $item['licence'] ?? null;
            if (empty($licence)) {
                $this->addError($attribute, 'Licence in state is required');
                return false;
            }

            if (!$licenseValidator->validate($licence)) {
                $this->addError($attribute, $licenseValidator->message);
                return false;
            }
        }
    }

    public function stateIdValidator($attribute, $params, $validator)
    {
        $attributeValue = $this->$attribute;

        foreach ($attributeValue as $item) {
            $stateId = $item['stateId'] ?? null;
            if (empty($stateId)) {
                $this->addError($attribute, 'State is Required');
            }

            if (!is_numeric((int)$stateId)) {
                $this->addError($attribute, 'Incorrect stateId provided');
            }

            $isExists = State::find()->andWhere(['id' => $stateId])->exists();
            if (!$isExists) {
                $this->addError($attribute, 'Incorrect state provided');
            }
        }
    }
}

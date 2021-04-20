<?php

namespace modules\account\models\forms\professional\educationCertification;

use backend\models\CompositeForm;
use modules\account\models\Account;
use Yii;

/**
 * Class EducationCertificationForm
 * @package modules\account\models\forms\professional\educationCertification
 *
 * @property-read  EducationForm[] $educationForms
 * @property-read  CertificationForm[] $certificationForms
 * @property-read  EducationCertificationOption $option
 */
class EducationCertificationForm extends CompositeForm
{
    /**
     * @var Account
     */
    protected $account;
    /**
     * @var EducationCertificationOption
     */
    protected EducationCertificationOption $option;

    /**
     * ProfessionalUpdateForm constructor.
     * @param Account $account
     * @param array $config
     * @throws \yii\base\InvalidConfigException
     */
    public function __construct(Account $account, $config = [])
    {
        $this->account = $account;
        if (!isset($this->option)) {
            $this->option = Yii::createObject(EducationCertificationOption::class);
        }
        $this->account = $account;
        parent::__construct($config);
    }

    public function init()
    {
        parent::init();
        $this->initCertificationForms();
        $this->initEducationForms();
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function initCertificationForms()
    {
        $certificationForms = self::createMultiple(CertificationForm::class, [], []);
        if (!$certificationForms) {
            foreach ($this->account->certifications as $certification) {
                $certificationForms[] = Yii::createObject([
                    'class' => CertificationForm::class,
                    'id' => $certification->id,
                    'certification' => $certification->certificateName,
                    'yearEarned' => $certification->yearReceived
                ]);
            }
        }

        if (!$certificationForms) {
            $certificationForms[] = Yii::createObject(CertificationForm::class);
        }
        $this->certificationForms = $certificationForms;
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function initEducationForms()
    {
        $educationForms = self::createMultiple(EducationForm::class, [], []);
        if (!$educationForms) {
            foreach ($this->account->educations as $education) {
                $certificationForms[] = Yii::createObject([
                    'class' => EducationForm::class,
                    'id' => $education->id,
                    'educationCollageId' => $education->collegeId,
                    'educationDegreeId' => $education->degreeId,
                    'graduated' => $education->graduated,
                ]);
            }
        }
        if (!$educationForms) {
            $educationForms[] = Yii::createObject(EducationForm::class);
        }

        $this->educationForms = $educationForms;
    }

    /**
     * @return EducationCertificationOption
     */
    public function getOption(): EducationCertificationOption
    {
        return $this->option;
    }

    /**
     * @return string[]
     */
    protected function internalForms()
    {
        return [
            'educationForms',
            'certificationForms',
        ];
    }
}

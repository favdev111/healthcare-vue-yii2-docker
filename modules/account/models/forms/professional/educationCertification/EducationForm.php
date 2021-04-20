<?php

namespace modules\account\models\forms\professional\educationCertification;

use backend\models\BaseForm;
use modules\account\models\ar\AccountEducation;
use modules\account\models\EducationCollege;
use modules\account\models\EducationDegree;

/**
 * Class EducationForm
 * @package modules\account\models\forms\professional\educationCertification
 */
class EducationForm extends BaseForm
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var int
     */
    public $educationCollageId;
    /**
     * @var int
     */
    public $educationDegreeId;
    /**
     * @var string
     */
    public $graduated;

    /**
     * @return \string[][]
     */
    public function rules()
    {
        return [
            ['graduated', 'number', 'min' => 1900, 'max' => date('Y')],
            ['id', 'exist', 'targetClass' => AccountEducation::class, 'targetAttribute' => 'id'],
            ['educationCollageId', 'exist', 'targetClass' => EducationCollege::class, 'targetAttribute' => 'id'],
            ['educationDegreeId', 'exist', 'targetClass' => EducationDegree::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'educationCollageId' => 'Collage',
            'educationDegreeId' => 'Degree',
        ];
    }
}

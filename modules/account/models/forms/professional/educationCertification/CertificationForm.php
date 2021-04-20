<?php

namespace modules\account\models\forms\professional\educationCertification;

use backend\models\BaseForm;
use modules\account\models\ar\AccountReward;

/**
 * Class CertificationForm
 * @package modules\account\models\forms\professional\educationCertification
 */
class CertificationForm extends BaseForm
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var int
     */
    public $certification;
    /**
     * @var string
     */
    public $yearEarned;

    /**
     * @return \string[][]
     */
    public function rules()
    {
        return [
            ['yearEarned', 'number', 'min' => 1900, 'max' => date('Y')],
            ['certification', 'string', 'max' => 254],
            ['id', 'exist', 'targetClass' => AccountReward::class, 'targetAttribute' => 'id'],
        ];
    }
}

<?php

namespace modules\account\models\forms\professional\role;

use backend\models\BaseForm;
use common\components\validators\LicenseValidator;
use modules\account\models\ar\AccountLicenceState;
use modules\account\models\ar\State;

/**
 * Class LicenceStateForm
 * @package modules\account\models\forms\professional\role
 */
class LicenseStateForm extends BaseForm
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $stateId;
    /**
     * @var string
     */
    public $license;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['stateId', 'license'], 'required'],
            ['stateId', 'exist', 'targetClass' => State::class, 'targetAttribute' => 'id'],
            ['id', 'exist', 'targetClass' => AccountLicenceState::class, 'targetAttribute' => 'id'],
            ['license', LicenseValidator::class],
        ];
    }

    public function attributeLabels()
    {
        return [
            'stateId' => 'State',
        ];
    }
}

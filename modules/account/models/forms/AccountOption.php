<?php

namespace modules\account\models\forms;

use backend\models\Option;
use common\helpers\AccountStatusHelper;
use modules\account\helpers\ConstantsHelper;

/**
 * Class AccountOption
 * @package modules\account\models\forms
 *
 * @property-read array $gender
 * @property-read array $statuses
 */
class AccountOption extends Option
{
    /**
     * @return array
     */
    public function getStatuses(): array
    {
        return AccountStatusHelper::getAllStatuses();
    }

    /**
     * @return array
     */
    public function getGender(): array
    {
        return ConstantsHelper::gender();
    }
}

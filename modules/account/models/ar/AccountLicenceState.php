<?php

namespace modules\account\models\ar;

use common\components\ActiveRecord;

/**
 * Class AccountLicenceState
 * @property int $id
 * @property int $stateId
 * @property int $accountId
 * @property string $licence
 * @package modules\account\models\ar
 */
class AccountLicenceState extends ActiveRecord
{
    public function getState()
    {
        return $this->hasOne(State::class, ['id' => 'stateId']);
    }
}

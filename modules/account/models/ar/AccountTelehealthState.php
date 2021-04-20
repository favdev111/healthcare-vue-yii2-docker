<?php

namespace modules\account\models\ar;

use common\components\ActiveRecord;

/**
 * Class AccountTelehealthState
 * @property int $id
 * @property int $stateId
 * @property-read State $state
 * @property int $accountId
 * @package modules\account\models\ar
 */
class AccountTelehealthState extends ActiveRecord
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(State::class, ['id' => 'stateId']);
    }
}

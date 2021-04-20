<?php

namespace modules\account\models\ar;

use common\components\ActiveRecord;

/**
 * Class AccountInsurance
 * @property $accountId
 * @property $insuranceCompanyId
 * @property-read \yii\db\ActiveQuery|InsuranceCompany $insuranceCompany
 * @property string $id [int unsigned]
 * @package modules\account\models\ar
 */
class AccountInsuranceCompany extends ActiveRecord
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInsuranceCompany()
    {
        return $this->hasOne(InsuranceCompany::class, ['id' => 'insuranceCompanyId']);
    }
}

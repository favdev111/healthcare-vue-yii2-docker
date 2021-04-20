<?php

namespace modules\account\models\ar;

use common\components\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * Class AccountLicenceState
 * @package modules\account\models\ar
 *
 * @property int $id
 * @property int $accountId
 * @property int $languageId
 * @property string $createdAt
 *
 * @property-read Language $language
 */
class AccountLanguage extends ActiveRecord
{
    /**
     * @return ActiveQuery
     */
    public function getLanguage()
    {
        return $this->hasOne(Language::class, ['languageId' => 'id']);
    }
}

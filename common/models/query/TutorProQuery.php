<?php

namespace common\models\query;

use common\components\ActiveQuery;
use common\models\TutorPro;
use modules\account\models\Account;
use modules\account\models\Role;
use Yii;

/**
 * Class TutorProQuery
 * @package common\models\query
 */
class TutorProQuery extends ActiveQuery
{
    /**
     * by new tutor pro requests
     * @return $this
     */
    public function new()
    {
        return $this->andWhere(['viewed' => TutorPro::STATUS_VIEWED_FALSE]);
    }
}

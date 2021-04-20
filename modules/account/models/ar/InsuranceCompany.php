<?php

namespace modules\account\models\ar;

use common\components\ActiveRecord;
use common\components\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * Class InsuranceCompany
 * @property int $id
 * @property string $name
 * @property string $createdAt
 * @property string $updatedAt
 * @property string $createdBy
 * @property string $updatedBy
 * @package modules\account\models\ar
 */
class InsuranceCompany extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
            'blameable' => [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'createdBy',
                'updatedByAttribute' => 'updatedBy',
                'value' => function () {
                    return \Yii::$app->user->id ?? null;
                }
            ],
        ];
    }
}

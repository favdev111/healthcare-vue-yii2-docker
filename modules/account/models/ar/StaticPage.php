<?php

namespace modules\account\models\ar;

use common\components\ActiveRecord;
use common\components\behaviors\TimestampBehavior;
use common\helpers\LandingPageHelper;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\SluggableBehavior;

/**
 * Class StaticPage
 * @package modules\account\models\ar
 * @property int $id
 * @property string $name
 * @property string $content
 * @property int $type
 * @property string $slug
 * @property string $seo_title
 * @property string $seo_description
 * @property string $createdAt
 * @property string $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 */
class StaticPage extends ActiveRecord
{
    public const TYPE_PRIVACY_POLICY = 1;
    public const TYPE_TERMS = 2;

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
            'slug' => [
                'class' => SluggableBehavior::class,
                'slugAttribute' => 'slug',
                'ensureUnique' => false,
                'value' => function () {
                    if ($this->slug) {
                        return $this->slug;
                    }

                    return LandingPageHelper::slug($this->name);
                },
            ],
        ];
    }
}

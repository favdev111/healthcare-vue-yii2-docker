<?php

namespace modules\account\models;

use common\helpers\LandingPageHelper;
use yii\behaviors\SluggableBehavior;

trait UpdateAllSlugsTrait
{
    /**
     * update field for all models
     */
    public static function updateAllSlugs()
    {
        foreach (self::find()->each() as $subject) {
            $subject->slug = null;
            $subject->save();
        }
    }

    public function getSlugBehavior()
    {
        return [
            'class' => SluggableBehavior::class,
            'ensureUnique' => false,
            'value' => function () {
                if ($this->slug) {
                    return $this->slug;
                }

                return LandingPageHelper::slug($this->name);
            },
        ];
    }

    public static function getAllSlugs()
    {
        $returnArray = [];
        $slugs = self::find()->select('slug')->asArray()->all();
        if (!empty($slugs)) {
            foreach ($slugs as $slug) {
                $returnArray[] = $slug['slug'];
            }
        }
        return $returnArray;
    }
}

<?php

namespace common\components;

use Yii;

trait UrlTrait
{
    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        $this->hash = static::generateHash($this->url);
        return parent::beforeValidate();
    }

    public static function findByUrl($url)
    {
        if (is_array($url)) {
            $in = array_map(
                function ($url) {
                    return static::generateHash($url);
                },
                $url
            );

            return static::find()->andWhere(['in', 'hash', $in])->limit(1)->one();
        }

        return static::findOne(['hash' => static::generateHash($url)]);
    }

    public static function generateHash($url)
    {
        return md5(mb_strtolower(trim($url, '/')));
    }
}

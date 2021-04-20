<?php

namespace modules\account\models;

use yii\helpers\ArrayHelper;

trait CreateMultipleTrait
{
    public static function createMultiple(array $multipleData = [])
    {
        $modelClass = self::class;
        $model    = new $modelClass();
        $formName = $model->formName();
        $post     = \Yii::$app->request->post($formName);
        $models   = [];

        if (! empty($multipleData)) {
            $keys = array_keys(ArrayHelper::map($multipleData, 'id', 'id'));
            $multipleData = array_combine($keys, $multipleData);
        }

        if ($post && is_array($post)) {
            foreach ($post as $i => $item) {
                if (isset($item['id']) && !empty($item['id']) && isset($multipleData[$item['id']])) {
                    $models[] = $multipleData[$item['id']];
                } else {
                    $models[] = new $modelClass();
                }
            }
        }

        unset($model, $formName, $post);

        return $models;
    }
}

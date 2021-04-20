<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class BaseForm
 * @package backend\models
 */
abstract class BaseForm extends Model
{
    /**
     * @var string
     */
    public $formName;

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function formName()
    {
        if ($this->formName) {
            return $this->formName . parent::formName();
        }
        return parent::formName();
    }

    /**
     * Creates and populates a set of models.
     *
     * @param string $modelClass
     * @param array $createParams
     * @param array $multipleModels
     * @param null $parentPath
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public static function createMultiple($modelClass, $createParams = [], $multipleModels = [], $pathToItems = null)
    {
        if (!Yii::$app->request->isPost) {
            return null;
        }
        $model = Yii::createObject($modelClass, $createParams);
        $formName = $model->formName() . $pathToItems;
        $post = Yii::$app->request->post();
        $post = ArrayHelper::getValue($post, $formName);
        $models = [];

        if (!empty($multipleModels)) {
            $keys = array_keys(ArrayHelper::map($multipleModels, 'id', 'id'));
            $multipleModels = array_combine($keys, $multipleModels);
        }

        if ($post && is_array($post)) {
            foreach ($post as $i => $item) {
                if (isset($item['id']) && !empty($item['id']) && isset($multipleModels[$item['id']])) {
                    $models[] = $multipleModels[$item['id']];
                } else {
                    $models[] = Yii::createObject($modelClass, $createParams);
                }
            }
        }

        unset($model, $formName, $post);

        return $models;
    }
}

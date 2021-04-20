<?php

namespace backend\components\widgets;

use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class ActiveForm
 * @package backend\components\widgets
 */
class ActiveForm extends \yii\bootstrap4\ActiveForm
{
    /**
     * @var bool
     */
    public $enableClientValidation = false;
    /**
     * @var bool
     */
    public $enableAjaxValidation = true;
    /**
     * @var int
     */
    public $scrollToErrorOffset = 30;
    /**
     * @var bool
     */
    public $validateOnChange = false;
    /**
     * @var bool
     */
    public $validateOnBlur = false;
    /**
     * @var bool
     */
    public $validateOnSubmit = true;
    /**
     * @var int
     */
    public $validationDelay = 0;

    /**
     * @param Model $model
     * @param null $attributes
     * @return array
     * @throws \Exception
     */
    public static function validate($model, $attributes = null)
    {
        $result = [];
        if ($attributes instanceof Model) {
            // validating multiple models
            $models = func_get_args();
            $attributes = null;
        } else {
            $models = [$model];
        }

        /* @var $model Model */
        foreach ($models as $model) {
            $model->validate($attributes);
            foreach ($model->getErrors() as $attribute => $errors) {
                $lastPos = strrpos($attribute, '.');
                if ($lastPos !== false) {
                    $attributeModelName = $attribute;

                    $attributeItems = explode('.', $attributeModelName);
                    $attributeItemsNew = [];
                    $attributeItems = array_reverse($attributeItems);
                    $attributeItemsNew[] = $attributeItems[0];

                    if (count($attributeItems) > 2) {
                        foreach ($attributeItems as $attributeItemIndex => $attributeItem) {
                            if (($attributeItemIndex %= 2) === 1) {
                                $attributeItemsNew[] = $attributeItem;
                            }
                        }
                    }

                    $attributeName = implode('.', array_reverse($attributeItemsNew));
                    $attributeModel = ArrayHelper::getValue($model, substr($attribute, 0, $lastPos));
                } else {
                    $attributeModel = $model;
                    $attributeName = $attribute;
                }
                $result[Html::getInputId($attributeModel, $attributeName)] = $errors;
            }
        }

        return $result;
    }
}

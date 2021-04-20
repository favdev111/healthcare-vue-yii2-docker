<?php

namespace backend\components\web;

/**
 * Class Request
 * @package backend\components\web
 *
 * @property-read bool $isFormValidate
 */
class Request extends \yii\web\Request
{
    /**
     * @return bool
     */
    public function getIsFormValidate(): bool
    {
        return $this->headers->has('X-Form-Validate');
    }
}

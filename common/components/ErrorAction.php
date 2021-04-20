<?php

namespace common\components;

use Yii;
use yii\base\Exception;

class ErrorAction extends \yii\web\ErrorAction
{
    /**
     * @inheritDoc
     */
    protected function getExceptionName()
    {
        if ($this->exception instanceof Exception) {
            $name = $this->exception->getName();
        } else {
            $name = $this->defaultName;
        }

        if ($code = $this->getExceptionCode()) {
            if ($code === 410) {
                $code = 404;
            }
            $name .= " (#$code)";
        }

        return $name;
    }
}

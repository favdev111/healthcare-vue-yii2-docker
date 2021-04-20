<?php

namespace common\components;

/**
 * Class Request
 * @package common\components
 */
class Request extends \yii\web\Request
{
    private $_resolve;

    public function resolve()
    {
        if ($this->_resolve === null) {
            $this->_resolve = parent::resolve();
        }

        return $this->_resolve;
    }
}

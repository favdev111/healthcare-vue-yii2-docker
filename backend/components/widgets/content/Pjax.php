<?php

namespace backend\components\widgets\content;

/**
 * Class Pjax
 * @package backend\components\widgets\content
 */
class Pjax extends \yii\widgets\Pjax
{
    /**
     * @var bool
     */
    public $timeout = false;
    /**
     * @var bool
     */
    public $enablePushState = true;
    /**
     * @var bool
     */
    public $enableReplaceState = false;
    /**
     * @var false[]
     */
    public $options = [
        'data-pjax-push-state' => false,
    ];
}

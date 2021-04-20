<?php

namespace common\components\behaviors;

use yii\base\Behavior;
use yii\base\InvalidCallException;
use yii\base\Application;

/**
 * Class ApplicationBehavior
 * @package common\components\behaviors
 */
abstract class ApplicationBehavior extends Behavior
{

    /**
     * @inheritdoc
     * @throws InvalidCallException
     */
    public function attach($owner)
    {
        if ($owner instanceof Application === false) {
            throw new InvalidCallException('Behaviors can be attached to Applications only');
        }
        parent::attach($owner);
    }
}

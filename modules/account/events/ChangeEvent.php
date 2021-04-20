<?php

namespace modules\account\events;

use yii\base\Event;

class ChangeEvent extends Event
{
    public $model;
    public $insert;

    /*
    * @param array $changedAttributes The old values of attributes that had changed.
    */
    public $changedAttributes;
}

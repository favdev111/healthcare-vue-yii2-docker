<?php

namespace common\components\behaviors;

use yii\behaviors\BlameableBehavior;

/**
 * Class CreatedUpdatedBehavior
 * @package common\components\behaviors
 */
class CreatedUpdatedBehavior extends BlameableBehavior
{
    /**
     * @var string
     */
    public $createdByAttribute = 'createdBy';
    /**
     * @var string
     */
    public $updatedByAttribute = 'updatedBy';
}

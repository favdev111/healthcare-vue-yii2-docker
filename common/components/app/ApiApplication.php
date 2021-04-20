<?php

namespace common\components\app;

use common\components\behaviors\ApplicationEventBehavior;

/**
 * Class ApiApplication
 * @package common\components\app
 */
class ApiApplication extends CommonApplication
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            ApplicationEventBehavior::class,
        ];
    }
}

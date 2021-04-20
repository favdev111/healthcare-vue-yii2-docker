<?php

namespace common\components\app;

use common\helpers\Url;
use yii\web\Application;
use Yii;

/**
 * Class CommonApplication
 * @package common\components\app
 *
 * @property-read \modules\account\Module $moduleAccount
 * @property-read \modules\core\Module $moduleCore
 * @property-read \modules\chat\Module $moduleChat
 */
class CommonApplication extends Application
{
    use CheckAppTrait;
    use ModuleAppTrait;

    public function getFrontendUrl($url)
    {
        return Url::getFrontendUrl($url);
    }
}

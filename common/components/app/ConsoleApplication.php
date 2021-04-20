<?php

namespace common\components\app;

class ConsoleApplication extends \yii\console\Application
{
    use CheckAppTrait;
    use ModuleAppTrait;
}

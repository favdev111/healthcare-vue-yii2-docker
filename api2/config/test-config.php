<?php

use common\components\app\Api2Application;

return yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/main.php',
    require __DIR__ . '/../../common/config/main.php',
    [
        'class' => Api2Application::class,
    ]
);

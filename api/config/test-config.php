<?php

use common\components\app\ApiApplication;

return yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/main.php',
    require __DIR__ . '/../../common/config/main.php',
    [
        'class' => ApiApplication::class,
    ]
);

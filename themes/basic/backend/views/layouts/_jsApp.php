<?php

$jaApp = [
    'constants' => require(__DIR__ . '/../../../common/views/layouts/_constants.php'),

];
echo '<script>var App = ' . \yii\helpers\Json::encode($jaApp) . ';</script>';

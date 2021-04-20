<?php

$rootDir = (dirname(dirname(__DIR__)));
require_once($rootDir . '/functions.php');

$dotenv = Dotenv\Dotenv::createImmutable($rootDir);
$dotenv->load();

Yii::setAlias('@root', $rootDir);
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@log', Yii::getAlias('@common') . DIRECTORY_SEPARATOR . 'runtime'
    . DIRECTORY_SEPARATOR . 'logs');
Yii::setAlias('@api', dirname(dirname(__DIR__)) . '/api');
Yii::setAlias('@api2', dirname(dirname(__DIR__)) . '/api2');
Yii::setAlias('@api2Patient', dirname(dirname(__DIR__)) . '/api2-patient');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('@modules', dirname(dirname(__DIR__)) . '/modules');
Yii::setAlias('@uploads', dirname(dirname(__DIR__)) . '/uploads');
Yii::setAlias('@themes', dirname(dirname(__DIR__)) . '/themes');
Yii::setAlias('@frontendUrl', env('FRONTEND_URL'));
Yii::setAlias('@backendUrl', env('BACKEND_URL'));
Yii::setAlias('@b2bUrl', env('B2B_URL'));

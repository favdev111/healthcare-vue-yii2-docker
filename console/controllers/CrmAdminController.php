<?php

namespace console\controllers;

use yii\console\Controller;
use yii\helpers\Console;
use yii\rbac\DbManager;

class CrmAdminController extends Controller
{
    public function actionCreate($email, $password)
    {
        $dbTransaction = \Yii::$app->db->beginTransaction();
        try {
            \Yii::$app->registration->createCrmAdmin($email, $password);
            $dbTransaction->commit();
            Console::output('Account created.');
        } catch (\Throwable $exception) {
            Console::output($exception->getMessage());
            $dbTransaction->rollBack();
        }
    }
}

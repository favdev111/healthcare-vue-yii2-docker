<?php

namespace console\controllers;

use common\models\Lead;
use yii\console\Controller;
use yii\db\Expression;
use yii\helpers\Console;

class LeadController extends Controller
{
    public function actionTryErrorLeadsResend($days = 30)
    {
        $leadQuery  = Lead::find()
            ->andWhere(['status' => Lead::QUEUE_STATUS_ERROR])
        ;

        if ($days > 0) {
            $leadQuery->andWhere([
                '>=',
                'createdAt',
                new Expression('DATE_ADD(NOW(), INTERVAL -' . $days . ' DAY)')
            ]);
        }
        foreach ($leadQuery->each(100) as $leadModel) {
            Console::output(
                'id=' . $leadModel->id . ', status=' . $leadModel->status . ', createdAt=' . \Yii::$app->formatter->asDatetime($leadModel->createdAt)
            );
            $leadModel->addToQueue(true);
        }
    }
}

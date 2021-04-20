<?php

namespace console\controllers;

use common\helpers\QueueHelper;
use common\models\Sms;
use yii\console\Controller;
use yii\helpers\Console;

class SmsMessageController extends Controller
{
    public function actionSendTutorHired()
    {
        $list = Sms::find()->followUpJobHiredTutor()->statusWaitingForSend()->createdForLast24hours();

        foreach ($list->each() as $sms) {
            QueueHelper::sendSms($sms);
        }
    }

    public function actionSetNoUserResponseStatus()
    {
        $query = Sms::find()
            ->andWhere(['type' => Sms::$typesWithUserResponse])
            ->sentMoreThan24Ago()
            ->byStatus(Sms::STATUS_SENT_TO_TWILIO)
            ->andWhere(['userResponseStatus' => null]);

        $count = 0;
        foreach ($query->each() as $sms) {
            /**
             * @var Sms $sms
             */
            $sms->userResponseStatus = Sms::RESPONSE_STATUS_NO_USER_RESPONSE;
            $sms->save(false);
            $count++;
        }
        Console::output('Count processed rows:' . $count);
    }
}

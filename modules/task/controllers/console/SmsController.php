<?php

namespace modules\task\controllers\console;

use modules\sms\components\Sms;
use UrbanIndo\Yii2\Queue\Worker\Controller;

class SmsController extends Controller
{
    public function actionSend($smsId)
    {
        /**
         * @var Sms $component
         */
        $component = \Yii::$app->sms;
        $sms = \common\models\Sms::findOne($smsId);
        if (empty($sms)) {
            \Yii::error('Queue:Failed to send sms, can\'t find sms with id = ' . $smsId, 'sms');
            return false;
        }
        if (!$component->sendSms($sms)) {
            \Yii::error('Queue:Sms with id = ' . $smsId . 'wasn\'t sent', 'sms');
            return false;
        }
        return true;
    }
}

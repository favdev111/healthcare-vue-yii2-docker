<?php

namespace modules\notification\controllers\console;

use common\helpers\Role;
use modules\account\models\backend\Account;
use modules\notification\models\notifications\CreditCardExpiredNotification;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class DefaultController
 * @package modules\notification\controllers\console
 */
class DefaultController extends Controller
{
    /**
     * Add new notification
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAdd()
    {
        $accountId = $this->prompt('Enter patient account id:');
        $account = Account::findOne($accountId);
        if (!$account || $account->roleId !== Role::ROLE_PATIENT) {
            $this->stdout('Account is invalid' . PHP_EOL, Console::FG_RED);
            return false;
        }

        $notifications = [
            CreditCardExpiredNotification::class
        ];
        $this->stdout('Choose notification class: ' . PHP_EOL);
        foreach ($notifications as $index => $notification) {
            $this->stdout($index, Console::FG_GREEN);
            $this->stdout(") " . $notification . PHP_EOL, Console::BOLD);
        }
        $indexNotification = $this->prompt('Enter notification number class:');
        $notificationClass = ArrayHelper::getValue($notifications, $indexNotification);

        if (!$notificationClass) {
            $this->stdout("Notification number ({$indexNotification}) is invalid" . PHP_EOL, Console::FG_RED);
            return false;
        }

        $notification = Yii::createObject($notificationClass);

        Yii::$app->notifier->send($account, $notification);
        $this->stdout("Success" . PHP_EOL, Console::FG_GREEN);
    }
}

<?php

namespace modules\account\controllers\console;

use common\components\Formatter;
use common\helpers\AdditionalDataHelper;
use modules\account\helpers\StartDateHelper;
use modules\account\models\AccountClientStatistic;
use modules\account\models\AccountScore;
use modules\account\models\AccountWithBlocked;
use modules\account\models\Profile;
use modules\account\Module;
use modules\notification\helpers\NotificationHelper;
use modules\notification\models\Notification;
use Yii;
use common\helpers\Role;
use modules\account\models\Account;
use yii\console\Controller;
use yii\helpers\Console;

class AccountController extends Controller
{
    public function actionReSaveProfileAddress($accountId)
    {
        $profile = Profile::find()
            ->andWhere(['accountId' => $accountId])
            ->limit(1)
            ->one();
        if (!$profile) {
            Console::output($accountId . ' - no such account found');
            return false;
        }

        if (empty($profile->address)) {
            Console::output($accountId . ' - address is empty');
            return false;
        }

        $geoData = Yii::$app->geocoding->getGeoData($profile->address);
        if (!empty($geoData)) {
            $profile->placeId = $geoData[0]->place_id;
            $profile->save(false);
            Console::output($accountId . ' - profile re-saved');
            return true;
        }

        Console::output($accountId . ' - no geo data for address');
    }

    public function actionReSaveCompanyClientProfilesAddress()
    {
        $query = AccountWithBlocked::find()->select(['id'])->isPatient()->asArray();
        foreach ($query->each(100) as $account) {
            $this->actionReSaveProfileAddress($account['id']);
        }
    }

    public function actionSetToZeroCountMessagesInStatistic()
    {
        AccountClientStatistic::updateAll([AccountClientStatistic::COUNTER_OF_MESSAGE_FROM_PROFILE_FORM => 0]);
    }

    public function actionClearOldStartDates()
    {
        /**
         * @var Formatter $formatter
         */
        $formatter = \Yii::$app->formatter;
        $currentDate = date($formatter->MYSQL_DATE . ' 12:00:00');
        $count = \Yii::$app->db
            ->createCommand()
            ->update(Profile::tableName(), ['startDate' => null], ['<', 'startDate', $currentDate])
            ->execute();
        Console::output('Count processed rows: ' . $count);
    }

    public function actionFillPhonesAndEmails()
    {
        AdditionalDataHelper::refillTables();
        AdditionalDataHelper::refillPhoneValidation();
    }
}

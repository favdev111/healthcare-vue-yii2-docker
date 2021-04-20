<?php

namespace modules\account\components;

use modules\account\models\Account as AccountAccount;

class TutorProfileUrlRule extends \yii\web\UrlRule
{
    public $pattern = "info/<stateCode>/<cityName>/ID<id:\d+>-<fullName>";

    public $route = 'account/profile-tutor/tutor-info';

    protected static $cache = [];

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        if ($route === $this->route && isset($params['id'])) {
            // In case current route is the required one add params based on Tutor ID
            // TODO: Find out how to get rid of this hack
            $params = array_merge(
                $params,
                $this->getParamsFromCache((int)$params['id'])
            );
        }
        return parent::createUrl($manager, $route, $params);
    }

    protected function getParamsFromCache($id)
    {
        if (isset(static::$cache[$id])) {
            return static::$cache[$id];
        }

        $params = [];
        $tutor = AccountAccount::findOneWithoutRestrictions($id);
        $params['stateCode'] = $tutor->profile->city->stateNameShort ?? 'CA';
        $params['cityName'] = !empty($tutor->profile->city->name) ? str_replace(' ', '-', $tutor->profile->city->name) : 'los-angeles';
        $params['fullName'] = $tutor->profile->firstName . '-' . $tutor->profile->lastName;

        static::$cache[$id] = $params;

        return static::$cache[$id];
    }
}

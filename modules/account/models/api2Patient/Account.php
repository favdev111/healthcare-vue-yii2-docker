<?php

namespace modules\account\models\api2Patient;

use modules\account\models\AccountAccessToken;
use yii\db\ActiveQuery;

/**
 * Class Account
 * @property $profile
 * @package modules\account\models\api2Patient
 */
class Account extends \modules\account\models\Account
{
    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($tokenString, $type = null)
    {
        $tokenModel = AccountAccessToken::find()->andWhere(['token' => $tokenString])->limit(1)->one();

        if (!$tokenModel) {
            return null;
        }

        return $tokenModel->account;
    }

    /**
     * @return ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(Profile::class, ['accountId' => 'id']);
    }
}

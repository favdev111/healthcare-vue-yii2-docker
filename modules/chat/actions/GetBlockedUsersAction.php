<?php

namespace modules\chat\actions;

use modules\account\models\Account;
use modules\chat\models\Chat;
use Yii;
use yii\base\Action;

class GetBlockedUsersAction extends Action
{
    public function run()
    {
        return Chat::find()->select('chatUserId')
            ->joinWith('account')
            ->andWhere(['not', [Account::tableName() . '.status' => Account::STATUS_ACTIVE]])
            ->orWhere(['in', 'email', Yii::$app->params['chatInvisibleAccounts']]) //@todo: Fix this logic in feature
            ->asArray()
            ->column();
    }
}

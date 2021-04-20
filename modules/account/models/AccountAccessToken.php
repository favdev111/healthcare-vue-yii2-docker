<?php

namespace modules\account\models;

use Yii;

/**
 * This is the model class for table "{{%account_access_token}}".
 *
 * @property int $id
 * @property int $accountId
 * @property string $token
 * @property string $deviceToken
 * @property int $platform
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @property-read Account $account
 */
class AccountAccessToken extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_access_token}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('account', 'ID'),
            'accountId' => Yii::t('account', 'User ID'),
            'token' => Yii::t('account', 'Token'),
            'deviceToken' => Yii::t('account', 'Device Token'),
            'platform' => Yii::t('account', 'Platform'),
            'createdAt' => Yii::t('account', 'Created At'),
            'updatedAt' => Yii::t('account', 'Updated At'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'common\components\behaviors\TimestampBehavior',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->generateNewToken();
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        $modelAccount = Yii::$app->getModuleAccount()->modelStatic('Account');
        return $this->hasOne($modelAccount, ['id' => 'accountId']);
    }

    public function generateNewToken()
    {
        $this->token = Yii::$app->security->generateRandomString(64);
    }

    public static function generateTokensForPush(int $accountId, bool $groupByPlatform = false)
    {
        if (!$groupByPlatform) {
            return static::find()
                ->andWhere(['accountId' => $accountId])
                ->select(['deviceToken'])
                ->asArray()
                ->column();
        }

        $tokens = [];
        $models = static::find()
            ->andWhere(['accountId' => $accountId])
            ->select(['deviceToken', 'platform'])
            ->asArray()
            ->all();

        foreach ($models as $model) {
            $platform = $model['platform'];
            $tokens[$platform][] = $model['deviceToken'];
        }

        return $tokens;
    }
}

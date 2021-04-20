<?php

namespace modules\account\models;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Yii;
use common\components\ActiveRecord;
use yii\db\StaleObjectException;
use Lcobucci\JWT\Builder;

/**
 * This is the model class for table "{{%account_token}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property integer $type
 * @property string $token
 * @property string $data
 * @property string $expiredAt
 * @property string $createdAt
 * @property string $updatedAt
 * @property boolean $status
 *
 * @property Account $account
 */
class Token extends ActiveRecord
{
    /**
     * @var int Token for email activations (for registrations)
     */
    const TYPE_EMAIL_ACTIVATE = 1;

    /**
     * @var int Token for email changes (on /account/account page)
     */
    const TYPE_EMAIL_CHANGE = 2;

    /**
     * @var int Token for password resets
     */
    const TYPE_PASSWORD_RESET = 3;

    /**
     * @var int Token for logging in via email
     */
    const TYPE_LOGIN = 4;

    /**
     * @var int Token for wrong email
     */
    const TYPE_WRONG_EMAIL = 5;

    /**
     * @var int API Token
     */
    const TYPE_TOKEN = 6;

    /**
     * @var int Payment Token
     */
    const TYPE_CLIENT_PAYMENT = 7;

    /**
     * @var int Apply to job without auth
     */
    const TYPE_JOB_APPLY = 8;

    /**
     * @var int
     */
    const TYPE_LEAVE_REVIEW = 9;

    /**
     * @var int
     */
    const TYPE_EMAIL_INACTIVE_ACCOUNT = 10;

    /**
     * @var int Status - Newly created token (not used)
     */
    const STATUS_NEW = 0;

    /**
     * @var int Status - Is used token
     */
    const STATUS_USED = 1;

    /**
     * @var int Status - Removed token
     */
    const STATUS_REMOVED = 9;

    /**
     * @var \modules\account\Module
     */
    public $module;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_token}}';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->module) {
            $this->module = Yii::$app->getModule('account');
        }
    }

    public function isUsed()
    {
        return $this->status === static::STATUS_USED;
    }

    public function isStatusNew()
    {
        return $this->status === static::STATUS_NEW;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'accountId' => 'User ID',
            'type' => 'Type',
            'token' => 'Token',
            'data' => 'Data',
            'status' => 'Status',
            'createdAt' => 'Created At',
            'expired_at' => 'Expired At',
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
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        $account = $this->module->model('account');
        return $this->hasOne($account::className(), ['id' => 'accountId']);
    }

    /**
     * Generate/reuse a token
     * @param int $accountId
     * @param int $type
     * @param string $data
     * @param string $expireTime
     * @return static
     * @throws \yii\base\Exception
     */
    public static function generate($accountId, $type, $data = null, $expireTime = null)
    {
        // attempt to find existing record
        // otherwise create new
        $checkExpiration = true;
        if ($accountId) {
            $model = static::findByAccountIdAndData($accountId, $data, $type, $checkExpiration);
            if ($model) {
                return $model;
            }
        } else {
            $model = static::findByAccount($data, $type, $checkExpiration);
        }
        if (!$model) {
            $model = new static();
        }

        // set/update data
        $model->accountId = $accountId;
        $model->type = $type;
        $model->data = $data;
        $model->createdAt = date('Y-m-d H:i:s');
        $model->expiredAt = $expireTime;
        $model->token = Yii::$app->security->generateRandomString();
        $model->status = self::STATUS_NEW;
        $model->save();
        return $model;
    }

    public function generateJwtToken()
    {
        $signer = new Sha512();

        return (new Builder())
            ->setIssuedAt(strtotime($this->createdAt))
            ->setExpiration(strtotime($this->expiredAt))
            ->setId($this->token)
            ->set('uid', $this->accountId)
            ->sign($signer, env('JWT_SIGN_KEY'))
            ->getToken();
    }

    public static function verifyJwtToken($tokenString)
    {
        $signer = new Sha512();
        $token = (new Parser())->parse((string) $tokenString);
        if (
            $token->isExpired()
            || !$token->verify($signer, env('JWT_SIGN_KEY'))
        ) {
            return false;
        }

        return [
            'accountId' => (int) $token->getClaim('uid'),
            'token' => (string) $token->getClaim('jti'),
        ];
    }

    /**
     * Find a token by specified field/value
     * @param string $field
     * @param string $value
     * @param array|int $type
     * @param bool $checkExpiration
     * @return static
     */
    public static function findBy($field, $value, $type, $checkExpiration)
    {
        $query = self::findByFieldQuery($field, $value, $type);
        if ($checkExpiration) {
            $query = self::checkExpiration($query);
        }
        return $query->limit(1)->one();
    }

    /**
     * @param $field
     * @param $value
     * @param $type
     * @return $this
     */
    public static function findByFieldQuery($field, $value, $type)
    {
        return static::find()->where([$field => $value, "type" => $type, 'status' => self::STATUS_NEW]);
    }

    public static function checkExpiration($query)
    {
        $now = date("Y-m-d H:i:s");
        $query->andWhere("([[expiredAt]] >= '$now' or [[expiredAt]] is NULL)");
        return $query;
    }

    public static function findByAccountIdAndData($accountId, $data, $type, $checkExpiration = true)
    {
        $query = self::findByFieldQuery('accountId', $accountId, $type);
        if ($checkExpiration) {
            $query = self::checkExpiration($query);
        }
        return $query->andWhere(['data' => $data])->limit(1)->one();
    }

    /**
     * Find a userToken by userId
     * @param int $userId
     * @param array|int $type
     * @param bool $checkExpiration
     * @return static
     */
    public static function findByUser($userId, $type, $checkExpiration = true)
    {
        return static::findBy("accountId", $userId, $type, $checkExpiration);
    }

    /**
     * Find a token by accountId
     * @param int $accountId
     * @param array|int $type
     * @param bool $checkExpiration
     * @return static
     */
    public static function findByAccount($accountId, $type, $checkExpiration = true)
    {
        return static::findBy("accountId", $accountId, $type, $checkExpiration);
    }

    /**
     * Find a token by token
     * @param string $token
     * @param array|int $type
     * @param bool $checkExpiration
     * @return static
     */
    public static function findByToken($token, $type, $checkExpiration = true)
    {
        return static::findBy("token", $token, $type, $checkExpiration);
    }

    /**
     * Find a token by data
     * @param string $data
     * @param array|int $type
     * @param bool $checkExpiration
     * @return static
     */
    public static function findByData($data, $type, $checkExpiration = true)
    {
        return static::findBy("data", $data, $type, $checkExpiration);
    }

    public function getNewEmail()
    {
        if ($this->type != self::TYPE_EMAIL_CHANGE) {
            return false;
        }
        return $this->getDataItem('newEmail');
    }

    public function getOldEmail()
    {
        if ($this->type != self::TYPE_EMAIL_CHANGE) {
            return false;
        }

        return $this->getDataItem('oldEmail');
    }

    protected function getDataItem($key)
    {
        $data = json_decode($this->data);
        return $data->$key ?? false;
    }

    public function markAsUsed()
    {
        if ($this->status != self::STATUS_NEW) {
            return false;
        }

        $this->status = self::STATUS_USED;
        return (bool)$this->update(false, ['status', 'updatedAt']);
    }

    public function delete()
    {
        if (in_array($this->status, [self::STATUS_USED, self::STATUS_REMOVED])) {
            return false;
        }

        if (!$this->beforeDelete()) {
            return false;
        }

        $this->status = self::STATUS_REMOVED;
        $result = $this->update(false, ['status']);
        if (!$result) {
            throw new StaleObjectException('The object being deleted is outdated.');
        }

        $this->setOldAttributes(null);
        $this->afterDelete();

        return $result;
    }
}

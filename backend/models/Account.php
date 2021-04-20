<?php

namespace backend\models;

use modules\account\models\Role;
use Yii;
use common\components\ActiveRecord;
use yii\bootstrap\Html;
use yii\console\Application as ConsoleApplication;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "account".
 *
 * @property string  $id
 * @property string  $email
 * @property string  $passwordHash
 * @property string  $firstName
 * @property string  $lastName
 * @property boolean $isActive
 * @property integer $createdAt
 * @property integer $updatedAt
 */
class Account extends ActiveRecord implements IdentityInterface
{
    const PRIMARY_KEY = 'id';

    const ROLESLABEL = [Role::ROLE_ADMIN => 'Admin', Role::ROLE_SEO => 'SEO', Role::ROLE_SUPER_ADMIN => 'Super Admin', Role::ROLE_TESTER_ADMIN => 'Tester Admin'];

    /**
     * @var string Current password - for account page updates
     */
    public $currentPassword;

    /**
     * @var string New password - for registration and changing password
     */
    public $newPassword;

    /**
     * @var string New password confirmation - for reset
     */
    public $newPasswordConfirm;

    /**
     * @var array Permission cache array
     */
    protected $permissionCache = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%backend_account}}';
    }

    public function init()
    {
        parent::init();
        $timeZone = 'America/Los_Angeles';
        Yii::$app->formatter->timeZone = $timeZone;
    }

    /**
     * @return array
     */
    public function getDropDownRoles()
    {
        $roles = self::ROLESLABEL;

        $isSuperAdmin = Yii::$app->user->identity->isSuperAdmin();

        if ($this->isCurrentUserModel() && $isSuperAdmin) {
            return [
                Role::ROLE_SUPER_ADMIN => $roles[Role::ROLE_SUPER_ADMIN],
            ];
        }

        unset($roles[Role::ROLE_SUPER_ADMIN]);
        return  $roles;
    }

    public function isTutor()
    {
        return $this->roleId == Role::ROLE_SPECIALIST;
    }
    /**
     * @return bool
     */
    public function isCurrentUserModel()
    {
        if ($this->isNewRecord) {
            return false;
        }
        return Yii::$app->user->identity->id == $this->id;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        // set initial rules
        $rules = [
            // general email rules
            [['email'], 'required'],
            [['email'], 'string', 'max' => 255],
            [['email'], 'unique'],
            [['email'], 'filter', 'filter' => 'trim'],
            [['email'], 'email'],

            // password rules
            [['newPassword'], 'string', 'min' => 6],
            [['newPassword'], 'required', 'on' => ['register', 'reset', 'changePassword']],
            [['newPasswordConfirm'], 'required', 'on' => ['register', 'reset', 'changePassword']],
            [['newPasswordConfirm'], 'required', 'when' => function ($model) {
                return $this->newPassword;
            }, 'whenClient' => 'function(attribute, value) {
                return $("#' . Html::getInputId($this, 'newPassword') . '").val() != "";
            }'
            ],
            [
                ['newPasswordConfirm'],
                'compare',
                'compareAttribute' => 'newPassword',
                'message'          => 'Passwords do not match',
            ],

            // account page
            [['firstName', 'lastName'], 'string', 'max' => 255],
            [['isActive'], 'boolean'],
            ['roleId', 'default', 'value' => Role::ROLE_ADMIN]
        ];

        // add required for currentPassword on account page
        // only if $this->passwordHash is set (might be null from a social login)
        if ($this->passwordHash) {
            $rules[] = [['currentPassword'], 'required', 'on' => ['account']];
        }

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['changePassword'] = ['newPassword', 'newPasswordConfirm', 'currentPassword'];

        return $scenarios;
    }

    /**
     * Validate current password (account page)
     */
    public function validateCurrentPassword()
    {
        if (!$this->validatePassword($this->currentPassword)) {
            $this->addError(
                "currentPassword",
                "Current password incorrect"
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'email'       => 'Email',
            'roleId'      => 'Role',
            // virtual attributes set above
            'newPassword' => $this->isNewRecord ? 'Password' : 'New Password',
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
    public static function findIdentity($id)
    {
        return static::findOne([
            'id' => $id,
            'isActive' => true,
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->id == $authKey;
    }

    /**
     * Validate password
     *
     * @param string $password
     *
     * @return bool
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword(
            $password,
            $this->passwordHash
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (
            $this->isSuperAdmin()
            && (1 != $this->isActive)
        ) {
            return false;
        }

        if (Yii::$app instanceof ConsoleApplication === false) {
            if (
                (Yii::$app->user->id == $this->id)
                && (1 != $this->isActive)
            ) {
                return false;
            }
        }


        // hash new password if set
        if ($this->newPassword) {
            $this->passwordHash = Yii::$app->security->generatePasswordHash($this->newPassword);
        }

        return parent::beforeSave($insert);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        $auth = Yii::$app->authManager;
        $auth->revokeAll($this->id);
        $auth->assign($auth->getRole($this->roleId), $this->id);

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if ($this->isSuperAdmin()) {
            return false;
        }

        return parent::beforeDelete();
    }

    public function isSuperAdmin()
    {
        return $this->roleId == Role::ROLE_SUPER_ADMIN;
    }

    public function isAdmin()
    {
        return $this->roleId == Role::ROLE_ADMIN;
    }

    public function isSeo()
    {
        return $this->roleId == Role::ROLE_SEO;
    }

    public function getDisplayName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Check if user can do specified $permission
     * @param string $permissionName
     * @param array $params
     * @param bool $allowCaching
     * @return bool
     */
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        // check for auth manager rbac
        // copied from \yii\web\User
        $auth = Yii::$app->getAuthManager();
        if ($auth) {
            if ($allowCaching && empty($params) && isset($this->permissionCache[$permissionName])) {
                return $this->permissionCache[$permissionName];
            }
            $access = $auth->checkAccess($this->getId(), $permissionName, $params);
            if ($allowCaching && empty($params)) {
                $this->permissionCache[$permissionName] = $access;
            }
            return $access;
        }

        // otherwise use our own custom permission (via the role table)
        return $this->roleId === $permissionName;
    }
}

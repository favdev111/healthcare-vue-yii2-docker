<?php

namespace modules\account\models;

use Yii;
use common\components\ActiveRecord;

/**
 * This is the model class for table "account_role".
 *
 * @property integer $id
 * @property string $name
 * @property string $createdAt
 * @property string $updatedAt
 */
class Role extends ActiveRecord
{
    /**
     * @var int Default user role
     */
    const ROLE_PATIENT = 1;

    /**
     * @var int Default user role
     */
    const ROLE_SPECIALIST = 2;

    const ROLE_ADMIN = 3;

    const ROLE_SEO = 4;

    const ROLE_SUPER_ADMIN = 5;

    const ROLE_TESTER_ADMIN = 6;

    const ROLE_CRM_ADMIN = 7;

    const ROLE_COMPANY_EMPLOYEE = 8;

    const ROLE_NAMES = [
        self::ROLE_PATIENT => 'Patient',
        self::ROLE_SPECIALIST => 'Specialist',
    ];

    const ROLE_NAME_ANONYMOUS = 'Anonymous';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_role}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
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
    public function getAccounts()
    {
        $account = $this->module->model('Account');
        return $this->hasMany($account::className(), ['roleId' => 'id']);
    }

    /**
     * Get list of roles for creating dropdowns
     * @return array
     */
    public static function dropdown()
    {
        // get all records from database and generate
        static $dropdown;
        if ($dropdown === null) {
            $models = static::find()->all();
            foreach ($models as $model) {
                $dropdown[$model->id] = $model->name;
            }
        }
        return $dropdown;
    }

    /**
     * @param $roleId
     * @return string
     */
    public static function getRoleNameById($roleId)
    {
        return static::ROLE_NAMES[$roleId] ?? static::ROLE_NAME_ANONYMOUS;
    }

    public function getRoleName()
    {
        return self::getRoleNameById($this->id);
    }
}

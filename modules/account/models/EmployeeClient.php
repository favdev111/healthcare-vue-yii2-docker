<?php

namespace modules\account\models;

use modules\account\behaviors\PositionBehavior;
use modules\account\models\api\AccountClient;
use modules\account\models\api\AccountEmployee;
use modules\labels\models\LabelRelationModel;

/**
 * This is the model class for table "{{%employee_clients}}".
 *
 * @property integer $id
 * @property integer $employeeId
 * @property integer $clientId
 * @property integer $position
 *
 * @property Account $client
 * @property Account $employee
 */
class EmployeeClient extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%employee_clients}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['clientId'], 'unique'],
            [['employeeId', 'clientId'], 'integer'],
            [
                ['employeeId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => \modules\account\models\api\Account::class,
                'targetAttribute' => ['employeeId' => 'id'],
                'filter' => function ($query) {
                    $query->leftJoin(
                        \modules\account\models\api\AccountTeam::tableName(),
                        'account_teams.accountId = account.id'
                    );
                    $query->andWhere([
                        'and',
                        [AccountTeam::tableName() . '.teamId' => Team::OPS_TEAM_ID],
                        [Account::tableName() . '.roleId' => Role::ROLE_COMPANY_EMPLOYEE],
                    ]);
                }
            ],
            [
                ['employeeId'],
                function () {
                    if (\modules\account\models\api\Account::findOne($this->employeeId)->isSuspended()) {
                        $this->addError('employeeId', 'This employee has been blocked');
                    }
                },
            ],
            [['clientId'], 'exist', 'skipOnError' => true, 'targetClass' => AccountClient::class, 'targetAttribute' => ['clientId' => 'id']],
            [['position'], 'default', 'value' => 1],
            [['position'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'employeeId' => 'Employee ID',
            'clientId' => 'Client ID',
            'position' => ' Position',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'positionBehavior' => [
                'class' => PositionBehavior::class,
                'positionAttribute' => 'position',
                'groupAttributes' => [
                    'employeeId'
                ],
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Account::class, ['id' => 'clientId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Account::class, ['id' => 'employeeId']);
    }

    /**
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function beforeDelete()
    {
        $labelRelation = LabelRelationModel::find()->andWhere(['itemId' => $this->clientId])->one();
        if ($labelRelation) {
            $labelRelation->delete();
        }
        return parent::beforeDelete();
    }
}

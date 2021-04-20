<?php

namespace modules\account\models\api2;

use modules\account\models\ar\AccountTelehealthState as Base;

/**
 * @OA\Schema(
 *   required={"stateId"},
 *   schema="AccountTelehealthModel",
 *   @OA\Property(
 *      property="stateId",
 *      description="State id",
 *      type="integer"
 *   ),
 * )
 */

/**
 * Class AccountTelehealthState
 * @package modules\account\models\api2
 * @property State $state
 */
class AccountTelehealthState extends Base
{
    public function fields()
    {
        return [
            'id',
            'accountId',
            'stateId',
            'stateName' => function () {
                return $this->state->name;
            }
        ];
    }

    public static function find()
    {
        return parent::find()->andWhere(['accountId' => \Yii::$app->user->id]);
    }

    public function getState()
    {
        return $this->hasOne(State::class, ['id' => 'stateId']);
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['stateId'], 'required'],
            [['stateId'], 'integer'],
            [['stateId'], 'exist', 'targetClass' => \modules\account\models\ar\State::class, 'targetAttribute' => 'id']
        ]);
    }
}

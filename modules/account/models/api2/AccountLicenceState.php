<?php

namespace modules\account\models\api2;

use modules\account\models\ar\AccountLicenceState as Base;

/**
 * @OA\Schema(
 *  required={"stateId", "licence"},
 *   schema="AccountLicenceModel",
 *   @OA\Property(
 *      property="stateId",
 *      description="State id",
 *       type="integer",
 *   ),
 *   @OA\Property (
 *      property="licence",
 *      description="Licence for this state",
 *      type="string"
 *     )
 * )
 */

/**
 * Class AccountLicenceState
 * @package modules\account\models\api2
 * @property State $state
 */
class AccountLicenceState extends Base
{
    public function fields()
    {
        return [
            'id',
            'accountId',
            'stateId',
            'licence',
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

<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *   schema="AccountRewardModel",
 *   @OA\Property(
 *      property="accountId",
 *      type="integer",
 *      description="Account id"
 *   ),
 *   @OA\Property(
 *      property="yearReceived",
 *      type="integer",
 *      description="year"
 *   ),
 *   @OA\Property(
 *      property="certificateName",
 *      type="string",
 *      description="Cerificate"
 *   ),
 * )
 */
class AccountReward extends \modules\account\models\ar\AccountReward
{
    public function fields()
    {
        return [
            'accountId',
            'certificateName',
            'yearReceived',
        ];
    }
}

<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *   required={"accountId", "healthGoalId"},
 *   schema="AccountHealthGoalModel",
 *    @OA\Property(
 *      property="healthGoalId",
 *      type="integer",
 *      description=""
 *   ),
 * )
 */
class AccountHealthGoal extends \modules\account\models\ar\AccountHealthGoal
{

}

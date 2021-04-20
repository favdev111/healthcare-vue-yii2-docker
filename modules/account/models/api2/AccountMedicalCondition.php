<?php

namespace modules\account\models\api2;

use common\components\ActiveRecord;

/**
 * @OA\Schema(
 *   required={"accountId", "medicalConditionId"},
 *   schema="AccountMedicalConditionModel",
 *    @OA\Property(
 *      property="medicalConditionId",
 *      type="integer",
 *      description=""
 *   ),
 * )
 */
class AccountMedicalCondition extends \modules\account\models\ar\AccountMedicalCondition
{

}

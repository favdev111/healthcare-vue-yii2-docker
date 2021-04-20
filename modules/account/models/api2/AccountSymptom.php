<?php

namespace modules\account\models\api2;

use common\components\ActiveRecord;

/**
 * @OA\Schema(
 *   required={"accountId", "symptomId"},
 *   schema="AccountSymptomModel",
 *    @OA\Property(
 *      property="symptomId",
 *      type="integer",
 *      description=""
 *   ),
 * )
 */
class AccountSymptom extends \modules\account\models\ar\AccountSymptom
{

}

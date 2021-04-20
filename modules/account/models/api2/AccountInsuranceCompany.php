<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *  required={"insuranceCompanyId"},
 *   schema="AccountInsuranceCompanyModel",
 *   @OA\Property(
 *      property="insuranceCompanyId",
 *      description="State id",
 *       type="integer",
 *   ),
 * )
 */
class AccountInsuranceCompany extends \modules\account\models\ar\AccountInsuranceCompany
{

}

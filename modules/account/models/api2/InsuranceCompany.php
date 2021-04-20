<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *   schema="InsuranceCompanyModel",
 *   @OA\Property(
 *      property="id",
 *      description="Insurance company Id",
 *      @OA\Schema(
 *          type="integer",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="name",
 *      description="Insurance company name",
 *      @OA\Schema(
 *          type="string",
 *      ),
 *   ),
 * )
 */

class InsuranceCompany extends \modules\account\models\ar\InsuranceCompany
{
    public function fields()
    {
        return [
            'id',
            'name',
        ];
    }
}

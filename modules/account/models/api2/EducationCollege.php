<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *   schema="EducationCollegeModel",
 *   @OA\Property(
 *      property="id",
 *      description="College ID",
 *      @OA\Schema(
 *         type="integer",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="name",
 *      description="College name",
 *      @OA\Schema(
 *         type="string",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="country",
 *      description="College country code",
 *      @OA\Schema(
 *         type="integer",
 *      ),
 *   ),
 * )
 */
class EducationCollege extends \modules\account\models\EducationCollege
{

}

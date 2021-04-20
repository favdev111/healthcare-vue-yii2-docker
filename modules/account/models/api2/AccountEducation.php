<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *   required={"accountId", "graduated", "collegeId"},
 *   schema="AccountEducationModel",
 *   @OA\Property(
 *      property="accountId",
 *      type="integer",
 *      description="Account id"
 *   ),
 *   @OA\Property(
 *      property="graduated",
 *      type="integer",
 *      description="Graduated year"
 *   ),
 *   @OA\Property(
 *      property="collegeId",
 *      type="integer",
 *      description="Id of a college"
 *   ),
 *    @OA\Property(
 *      property="degreeId",
 *      type="integer",
 *      description="Id of a college"
 *   ),
 * )
 */

/**
 * Class AccountEducation
 * @property EducationCollege $college
 * @package modules\account\models\api2
 */
class AccountEducation extends \modules\account\models\ar\AccountEducation
{
    public function fields()
    {
        return [
            'accountId',
            'collegeId',
            'collegeName' => function () {
                return $this->college->name;
            },
            'degreeId',
            'graduated',
        ];
    }

    public function getCollege()
    {
        return $this->hasOne(EducationCollege::class, ['id' => 'collegeId']);
    }
}

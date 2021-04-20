<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *   schema="ListPatientModel",
 *   @OA\Property(
 *      property="id",
 *      description="ListPatient id",
 *      @OA\Schema(
 *          type="integer",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="email",
 *      description="ListPatient email",
 *      @OA\Schema(
 *          type="string",
 *      ),
 *   ),
 *  @OA\Property(
 *      property="roleId",
 *      description="ListPatient roleId",
 *      @OA\Schema(
 *          type="integer",
 *      ),
 *   ),
 * )
 */
class ListPatientModel extends \modules\account\models\ar\ListPatient
{
    
}

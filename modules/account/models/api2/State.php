<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *   schema="StateModel",
 *   @OA\Property(
 *      property="id",
 *      description="State id",
 *      @OA\Schema(
 *          type="integer",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="name",
 *      description="State name",
 *      @OA\Schema(
 *          type="string",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="shortName",
 *      description="State short name",
 *      @OA\Schema(
 *          type="string",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="slug",
 *      description="State slug",
 *      @OA\Schema(
 *          type="string",
 *      ),
 *   ),
 * )
 */
class State extends \modules\account\models\ar\State
{

}

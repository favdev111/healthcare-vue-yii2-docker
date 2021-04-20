<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *   schema="AutoimmuneDiseaseModel",
 *   @OA\Property(
 *      property="id",
 *      description="id",
 *      @OA\Schema(
 *          type="integer",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="name",
 *      description="name",
 *      @OA\Schema(
 *          type="string",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="description",
 *      description="Description",
 *      @OA\Schema(
 *          type="string",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="slug",
 *      description="Slug",
 *      @OA\Schema(
 *          type="string",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="updatedAt",
 *      description="Updated at",
 *      @OA\Schema(
 *          type="string",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="createdAt",
 *      description="Created at",
 *      @OA\Schema(
 *          type="string",
 *      ),
 *   ),
 * )
 */
class AutoimmuneDisease extends \common\models\health\AutoimmuneDisease
{

}

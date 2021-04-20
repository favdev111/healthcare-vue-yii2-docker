<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *   schema="StaticPageModel",
 *   @OA\Property(
 *      property="id",
 *      description="Page id",
 *      @OA\Schema(
 *          type="integer",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="name",
 *      description="Page name",
 *      @OA\Schema(
 *          type="string",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="content",
 *      description="Page content",
 *      @OA\Schema(
 *          type="string",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="slug",
 *      description="Page slug",
 *      @OA\Schema(
 *          type="string",
 *      ),
 *   ),
 * )
 */
class StaticPage extends \modules\account\models\ar\StaticPage
{

}

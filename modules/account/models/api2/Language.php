<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *   schema="LanguageModel",
 *   @OA\Property(
 *      property="id",
 *      description="Language id",
 *      @OA\Schema(
 *          type="integer",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="name",
 *      description="Language name",
 *      @OA\Schema(
 *          type="string",
 *      ),
 *   ),
 * )
 */
class Language extends \modules\account\models\ar\Language
{

}

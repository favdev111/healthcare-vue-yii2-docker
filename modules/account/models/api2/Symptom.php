<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *   schema="SymptomModel",
 *   @OA\Property(
 *      property="id",
 *      description="Symptom id",
 *      @OA\Schema(
 *          type="integer",
 *      ),
 *   ),
 *   @OA\Property(
 *      property="name",
 *      description="Symptom name",
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
 * )
 */
class Symptom extends \common\models\health\Symptom
{
    public function fields()
    {
        return [
            'id',
            'name',
            'description',
            'slug',
        ];
    }
}

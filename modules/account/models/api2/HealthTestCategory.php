<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *   schema="HealthTestCategoryModel",
 *   @OA\Property(
 *      property="id",
 *      description="id",
 *      type="integer"
 *   ),
 *   @OA\Property(
 *      property="name",
 *      description="Health test category name",
 *      type="string"
 *   )
 * )
 */
class HealthTestCategory extends \common\models\health\HealthTestCategory
{
    public function fields()
    {
        return [
            'id',
            'name'
        ];
    }
}

<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *   schema="HealthTestModel",
 *   @OA\Property(
 *      property="id",
 *      description="Health test",
 *      type="integer"
 *   ),
 *   @OA\Property(
 *      property="name",
 *      description="Health test name",
 *      type="string"
 *   )
 * )
 */
class HealthTest extends \common\models\health\HealthTest
{
    public function fields()
    {
        return [
            'id',
            'name'
        ];
    }
}

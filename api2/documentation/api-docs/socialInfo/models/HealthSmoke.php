<?php

/**
 * @OA\Schema(
 *   schema="HealthSmokeModel",
 *   required={"id", "name"},
 *   @OA\Property(
 *      property="id",
 *      type="integer",
 *      example=1,
 *      description="Unique identificator"
 *   ),
 *   @OA\Property(
 *      property="name",
 *      type="string",
 *      example="currently",
 *      description="Smoke status"
 *   )
 * )
 */

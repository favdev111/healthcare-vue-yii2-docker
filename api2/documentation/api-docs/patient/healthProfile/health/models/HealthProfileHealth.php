<?php

/**
 * @OA\Schema(
 *   schema="HealthProfileHealthModel",
 *   required={"isOtherSubstance"},
 *   @OA\Property(
 *       property="smoke",
 *       ref="#/components/schemas/HealthSmokeModel",
 *   ),
 *   @OA\Property(
 *       property="drink",
 *       ref="#/components/schemas/HealthDrinkModel",
 *   ),
 *   @OA\Property(
 *      property="isOtherSubstance",
 *      type="boolean",
 *      example=true,
 *      description="
 *      true - has other substance
 *      false - has no"
 *   ),
 *   @OA\Property(
 *      property="otherSubstanceText",
 *      type="string",
 *      example="Just text",
 *      description="This property is full in case property isOtherSubstance == true"
 *   )
 * )
 */

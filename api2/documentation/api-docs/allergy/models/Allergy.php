<?php

/**
 * @OA\Schema(
 *   schema="AllergyModel",
 *   required={"id", "name", "allergyCategoryId"},
 *   @OA\Property(
 *      property="id",
 *      type="integer",
 *      example=1,
 *      description="Unique identificator"
 *   ),
 *   @OA\Property(
 *      property="name",
 *      type="string",
 *      example="Wheat starch",
 *      description="Allergy name"
 *   ),
 *   @OA\Property(
 *      property="allergyCategoryId",
 *      type="integer",
 *      example=1,
 *      description="ID of allergy category"
 *   ),
 * )
 */

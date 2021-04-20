<?php

/**
 * @OA\Schema(
 *   schema="AllergyCategoryModel",
 *   required={"id", "name", "isMedicalGroup"},
 *   @OA\Property(
 *      property="id",
 *      type="integer",
 *      example=1,
 *      description="Unique identificator"
 *   ),
 *   @OA\Property(
 *      property="name",
 *      type="string",
 *      example="Gluten",
 *      description="Allergy category name"
 *   ),
 *   @OA\Property(
 *      property="isMedicalGroup",
 *      type="boolean",
 *      example=true,
 *      description="It's using for medical health group.
 *      true - should set allergy category group
 *      false - should set allergy items"
 *   )
 * )
 */

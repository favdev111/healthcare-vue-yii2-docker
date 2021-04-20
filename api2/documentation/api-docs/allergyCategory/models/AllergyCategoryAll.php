<?php

/**
 * @OA\Schema(
 *   schema="AllergyCategoryAllModel",
 *   required={"category", "items"},
 *   @OA\Property(
 *       required={"id", "name"},
 *       property="category",
 *       @OA\Property(
 *           property="id",
 *           type="integer",
 *           description="Unique identificator",
 *           example=1
 *       ),
 *       @OA\Property(
 *           property="title",
 *           type="string",
 *           description="title",
 *           example="Category name"
 *       ),
 *       @OA\Property(
 *           property="hasItems",
 *           type="boolean",
 *           description="In case category has items",
 *           example=true
 *       )
 *   ),
 *   @OA\Property(
 *     property="items",
 *     type="array",
 *     @OA\Items(
 *       description="List of alergies",
 *       type="object",
 *       @OA\Property(
 *           property="id",
 *           type="integer",
 *           description="Unique identificator",
 *           example=1
 *       ),
 *       @OA\Property(
 *           property="name",
 *           type="string",
 *           description="name",
 *           example="Name of allergy"
 *       ),
 *     ),
 *   )
 * )
 */

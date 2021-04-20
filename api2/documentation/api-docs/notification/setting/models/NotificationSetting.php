<?php

/**
 * @OA\Schema(
 *   required={"id", "name", "category", "isSet"},
 *   schema="NotificationSettingModel",
 *   @OA\Property(
 *      property="id",
 *      type="integer",
 *      example=1,
 *      description="Unique identificator"
 *   ),
 *   @OA\Property(
 *      property="name",
 *      type="string",
 *      example="Payroll issued",
 *      description="Name of setting"
 *   ),
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
 *           property="name",
 *           type="string",
 *           description="Name",
 *           example="Setting category name"
 *       )
 *   ),
 *   @OA\Property(
 *      property="isSet",
 *      type="boolean",
 *      example="true",
 *      description="Is set value by current auth account"
 *   ),
 * )
 */

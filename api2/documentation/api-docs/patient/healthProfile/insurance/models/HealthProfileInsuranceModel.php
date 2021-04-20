<?php

/**
 * @OA\Schema(
 *   schema="HealthProfileInsuranceModel",
 *   required={"id", "firstName", "lastName"},
 *   @OA\Property(
 *      property="id",
 *      type="integer",
 *      example=3,
 *      description="Unique identificator"
 *   ),
 *   @OA\Property(
 *      property="firstName",
 *      type="string",
 *      example="John",
 *      description="Insured’s first name"
 *   ),
 *   @OA\Property(
 *      property="lastName",
 *      type="string",
 *      example="Doe",
 *      description="Insured’s last name"
 *   ),
 *   @OA\Property(
 *      property="socialSecurityNumber",
 *      type="string",
 *      example="777-CC",
 *      description="Insured’s social security number"
 *   ),
 *   @OA\Property(
 *      property="isPrimary",
 *      type="boolean",
 *      example=true,
 *      description="Primary(true) or secondary(false) insurance"
 *   ),
 *   @OA\Property(
 *      property="policyNumber",
 *      type="string",
 *      example="12345-BX",
 *      description="Policy number"
 *   ),
 *   @OA\Property(
 *      property="groupNumber",
 *      type="string",
 *      example="765XX321",
 *      description="Group number"
 *   ),
 *   @OA\Property(
 *      property="dateOfBirth",
 *      type="string",
 *      example="1990-01-30",
 *      description="Insured’s date of birth. Format: Y-m-d"
 *   ),
 *   @OA\Property(
 *       required={"id", "name"},
 *       property="insuranceCompany",
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
 *           example="Aexcel Plus Choice POS II (Aetna HealthFund)"
 *       )
 *   ),
 *   @OA\Property(
 *       required={"zipCode", "city", "address", "googlePlaceId"},
 *       property="location",
 *       @OA\Property(
 *           property="zipCode",
 *           type="integer",
 *           description="Zip code",
 *           example=60614
 *       ),
 *       @OA\Property(
 *           property="city",
 *           type="string",
 *           description="City",
 *           example="Chicago"
 *       ),
 *       @OA\Property(
 *           property="address",
 *           type="string",
 *           description="Address",
 *           example="1111 W Diversey Pkwy, Chicago, IL"
 *       ),
 *       @OA\Property(
 *           property="googlePlaceId",
 *           type="string",
 *           description="Google place unique indentity",
 *           example="ChIJmalJpQHTD4gRAnFpF-iJcng"
 *       )
 *   ),
 * )
 */

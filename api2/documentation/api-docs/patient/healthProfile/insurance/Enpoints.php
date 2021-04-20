<?php

/**
 * @OA\Get(
 *     path="/patient/health-profile-insurances",
 *     tags={"HealthProfileInsurance"},
 *     summary="Get list of health profile insurances",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Response(
 *         response="200",
 *         description="Successful result",
 *         @OA\JsonContent(ref="#/components/schemas/HealthProfileInsuranceResponse")
 *     )
 * )
 */

/**
 * @OA\Get(
 *     path="/patient/health-profile-insurances/{insuranceId}",
 *     tags={"HealthProfileInsurance"},
 *     summary="Get health profile insurance",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="Insurance id",
 *         in="path",
 *         name="insuranceId",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Successful result",
 *         @OA\JsonContent(ref="#/components/schemas/HealthProfileInsuranceResponse")
 *     )
 * )
 */

/**
 * @OA\Put (
 *     path="/patient/health-profile-insurances/{insuranceId}",
 *     tags={"HealthProfileInsurance"},
 *     summary="Update insurance",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="Insurance id",
 *         in="path",
 *         name="insuranceId",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\RequestBody(
 *            @OA\MediaType (
 *              mediaType="application/json",
 *               @OA\Schema(
 *                      type="object",
 *                      @OA\Property(
 *                          property="insuranceCompanyId",
 *                          description="Insurance company Id",
 *                          type="integer"
 *                      ),
 *                      @OA\Property(
 *                          property="groupNumber",
 *                          description="Group name",
 *                          type="string"
 *                      ),
 *                      @OA\Property(
 *                          property="policyNumber",
 *                          description="policy number",
 *                          type="string"
 *                      ),
 *                      @OA\Property(
 *                          property="googlePlaceId",
 *                          description="google place Id",
 *                          type="string"
 *                      ),
 *                      @OA\Property(
 *                          property="firstName",
 *                          description="First name",
 *                          type="string"
 *                      ),
 *                      @OA\Property(
 *                          property="lastName",
 *                          description="Last name",
 *                          type="string"
 *                      ),
 *                      @OA\Property(
 *                          property="socialSecurityNumber",
 *                          description="Social security number",
 *                          type="string"
 *                      ),
 *                      @OA\Property(
 *                          property="dateOfBirth",
 *                          description="Birth date",
 *                          type="string"
 *                      )
 *                )
 *           )
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Successful result",
 *         @OA\JsonContent(ref="#/components/schemas/HealthProfileInsuranceResponse")
 *     ),
 *     @OA\Response(response="404", description="Object not found: Insurance id"),
 *     @OA\Response(
 *        response=422,
 *        description="Validation errors",
 *        @OA\Schema(ref="#/components/schemas/ValidationError"),
 *    )
 * )
 */

/**
 * @OA\Post (
 *     path="/patient/health-profile-insurances",
 *     tags={"HealthProfileInsurance"},
 *     summary="Add insurance",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\RequestBody(
 *            @OA\MediaType (
 *              mediaType="application/json",
 *               @OA\Schema(
 *                      required={
 *                          "insuranceCompanyId",
 *                          "groupNumber",
 *                          "policyNumber",
 *                          "googlePlaceId",
 *                          "firstName",
 *                          "lastName",
 *                          "socialSecurityNumber",
 *                          "dateOfBirth",
 *                          "isPrimary"
 *                      },
 *                      type="object",
 *                      @OA\Property(
 *                          property="insuranceCompanyId",
 *                          description="Insurance company Id",
 *                          type="integer"
 *                      ),
 *                      @OA\Property(
 *                          property="groupNumber",
 *                          description="Group name",
 *                          type="string"
 *                      ),
 *                      @OA\Property(
 *                          property="policyNumber",
 *                          description="policy number",
 *                          type="string"
 *                      ),
 *                      @OA\Property(
 *                          property="googlePlaceId",
 *                          description="google place Id",
 *                          type="string"
 *                      ),
 *                      @OA\Property(
 *                          property="firstName",
 *                          description="First name",
 *                          type="string"
 *                      ),
 *                      @OA\Property(
 *                          property="lastName",
 *                          description="Last name",
 *                          type="string"
 *                      ),
 *                      @OA\Property(
 *                          property="socialSecurityNumber",
 *                          description="Social security number",
 *                          type="string"
 *                      ),
 *                      @OA\Property(
 *                          property="dateOfBirth",
 *                          description="Birth date",
 *                          type="string"
 *                      ),
 *                      @OA\Property(
 *                          property="isPrimary",
 *                          description="Primary(1) or secondary(0) insurance",
 *                          type="integer"
 *                      )
 *                )
 *           )
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Successful result",
 *         @OA\JsonContent(ref="#/components/schemas/HealthProfileInsuranceResponse")
 *     ),
 *     @OA\Response(response="404", description="Object not found: Insurance id"),
 *     @OA\Response(
 *        response=422,
 *        description="Validation errors",
 *        @OA\Schema(ref="#/components/schemas/ValidationError"),
 *    )
 * )
 */

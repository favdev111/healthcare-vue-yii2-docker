<?php

/**
 * @OA\Patch(path="/health-pros/profile",
 *     tags={"Health Pros Profile"},
 *     security={{"Bearer":{}}},
 *     summary="Fill Form",
 *     description="",
 *     @OA\RequestBody(
 *      required=true,
 *          @OA\MediaType (
 *           mediaType="application/json",
 *         @OA\Schema(
 *              type="object",
 *                @OA\Property(
 *                   property="firstName",
 *                   type="string"
 *               ),
 *               @OA\Property(
 *                   property="lastName",
 *                   type="string"
 *               ),
 *               @OA\Property(
 *                   property="phoneNumber",
 *                   type="string"
 *               ),
 *               @OA\Property(
 *                   property="gender",
 *                   type="string"
 *               ),
 *               @OA\Property(
 *                   property="dateOfBirth",
 *                   type="string",
 *                   description="Y-m-d"
 *               ),
 *              @OA\Property(
 *                   property="placeId",
 *                   type="string"
 *               ),
 *               )
 *          ),
 *    ),
 *    @OA\Response(
 *        response="200",
 *        description="Success",
 *    ),
 *    @OA\Response(
 *        response=422,
 *        description="Validation errors",
 *        @OA\Schema(ref="#/components/schemas/ValidationError"),
 *    )
 * )
 */

/**
 * @OA\Patch(path="/health-pros/role",
 *     tags={"Health Pros Role"},
 *     security={{"Bearer":{}}},
 *     summary="Fill Form",
 *     description="",
 *     @OA\RequestBody(
 *           required=true,
 *          @OA\MediaType (
 *           mediaType="application/json",
 *            @OA\Schema(
 *            type="object",
 *           @OA\Property(
 *              property="professionalTypeId",
 *              type="integer"
 *          ),
 *          @OA\Property(
 *              property="doctorTypeId",
 *              type="integer",
 *              description="Used for professionalTypeId is 1(doctor) or 3(nurse practitioner). For nurse is specialty",
 *          ),
 *          @OA\Property(
 *              property="npiNumber",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="yearsOfExperience",
 *              type="integer"
 *          ),
 *          @OA\Property(
 *              property="isBoardCertified",
 *              type="integer",
 *          ),
 *         @OA\Property(
 *              property="hasDisciplinaryAction",
 *              type="integer"
 *          ),
 *          @OA\Property(
 *              property="disciplinaryActionText",
 *              type="string"
 *          ),
 *         @OA\Property(
 *              property="currentlyEnrolled",
 *              type="integer"
 *          ),
 *         @OA\Property(
 *              property="insuranceCompanies",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/AccountInsuranceCompanyModel"),
 *          ),
 *        @OA\Property(
 *              property="insuranceCompanyText",
 *              type="string"
 *          ),
 *         @OA\Property(
 *              property="licenceStates",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/AccountLicenceModel"),
 *          ),
 *         @OA\Property(
 *              property="telehealthStates",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/AccountTelehealthModel"),
 *          ),
 *         )
 *        )
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="Success",
 *    ),
 *    @OA\Response(
 *        response=422,
 *        description="Validation errors",
 *        @OA\Schema(ref="#/components/schemas/ValidationError"),
 *    )
 * )
 */

/**
 * @OA\Patch(path="/health-pros/specification",
 *     tags={"Health Pros Specification"},
 *     security={{"Bearer":{}}},
 *     summary="Fill Form",
 *     description="",
 *     @OA\RequestBody(
 *          @OA\MediaType (
 *               mediaType="application/json",
 *               @OA\Schema(
 *            type="object",
 *         @OA\Property(
 *              property="healthTests",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/AccountHealthTestModel"),
 *          ),
 *         @OA\Property(
 *              property="symptoms",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/AccountSymptomModel"),
 *          ),
 *         @OA\Property(
 *              property="medicalConditions",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/AccountMedicalConditionModel"),
 *          ),
 *         @OA\Property(
 *              property="autoimmuneDiseases",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/AccountAutoimmuneDiseaseModel"),
 *          ),
 *         @OA\Property(
 *              property="healthGoals",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/AccountHealthGoalModel"),
 *          ),
 *         )
 *          )
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="Success",
 *    ),
 *    @OA\Response(
 *        response=422,
 *        description="Validation errors",
 *        @OA\Schema(ref="#/components/schemas/ValidationError"),
 *    )
 * )
 */

/**
 * @OA\Post(path="/health-pros/avatar",
 *     tags={"Health Pros Avatar"},
 *     security={{"Bearer":{}}},
 *     summary="Fill Form",
 *     description="",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     description="file to upload",
 *                     property="file",
 *                     type="string",
 *                     format="file",
 *                 ),
 *                 required={"file"}
 *             )
 *         )
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="Success",
 *    ),
 *    @OA\Response(
 *        response=422,
 *        description="Validation errors",
 *        @OA\Schema(ref="#/components/schemas/ValidationError"),
 *    )
 * )
 */

/**
 * @OA\Post(path="/health-pros/rate-and-policy",
 *     tags={"Health Pros Rate and Policy"},
 *     security={{"Bearer":{}}},
 *     summary="Fill Form",
 *     description="",
 *     @OA\RequestBody(
 *        @OA\MediaType (
 *          mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/HealthProsRateModel")
 *      )
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="Success",
 *    ),
 *    @OA\Response(
 *        response=422,
 *        description="Validation errors",
 *        @OA\Schema(ref="#/components/schemas/ValidationError"),
 *    )
 * )
 */

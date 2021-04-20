<?php

/**
 * @OA\Post (
 *     path="/patient/health-profiles/{id}",
 *     tags={"HealthProfile"},
 *     summary="Update health profile general info",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="Health profile id",
 *         in="path",
 *         name="id",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\RequestBody(
 *        @OA\MediaType(
 *            mediaType="application/json",
 *            @OA\Schema(
 *                required={"firstName"},
 *                @OA\Property(
 *                    property="firstName",
 *                    type="string",
 *                ),
 *                @OA\Property(
 *                    property="lastName",
 *                    type="string",
 *                ),
 *                @OA\Property(
 *                    property="birthday",
 *                    type="string",
 *                    example="11/11/1985",
 *                    description="In format m/d/Y",
 *                ),
 *                @OA\Property(
 *                    property="phoneNumber",
 *                    type="string",
 *                    example="+108888000000",
 *                ),
 *                @OA\Property(
 *                    property="email",
 *                    type="string",
 *                    example="winit@eltexsft.com",
 *                ),
 *                @OA\Property(
 *                    property="height",
 *                    type="number",
 *                    example="96",
 *                    description="1ft === 12in, example 6' 11 => 6*12 => 72"
 *                ),
 *                @OA\Property(
 *                    property="weight",
 *                    type="number",
 *                    example="66",
 *                ),
 *                @OA\Property(
 *                    property="gender",
 *                    type="integer",
 *                    example="1",
 *                ),
 *                @OA\Property(
 *                    property="googlePlaceId",
 *                    type="string",
 *                    description="https://developers.google.com/places/web-service/place-id",
 *                ),
 *                @OA\Property(
 *                    property="maritalStatusId",
 *                    type="integer",
 *                    example="1",
 *                ),
 *                @OA\Property(
 *                    property="educationLevelId",
 *                    type="integer",
 *                    example="1",
 *                ),
 *                @OA\Property(
 *                    property="childrenCount",
 *                    type="integer",
 *                    example="0",
 *                ),
 *                @OA\Property(
 *                    property="occupation",
 *                    type="string",
 *                ),
 *                @OA\Property(
 *                    property="employer",
 *                    type="string",
 *                ),
 *                @OA\Property(
 *                    property="relationshipId",
 *                    type="integer",
 *                ),
 *            )
 *        )
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Successful result",
 *         @OA\JsonContent(ref="#/components/schemas/HealthProfileGeneralResponse")
 *     )
 * )
 */

/**
 * @OA\Get (
 *     path="/patient/health-profiles/{id}",
 *     tags={"HealthProfile"},
 *     summary="Get health profile general info",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="Health profile id",
 *         in="path",
 *         name="id",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Successful result",
 *         @OA\JsonContent(ref="#/components/schemas/HealthProfileGeneralResponse")
 *     )
 * )
 */

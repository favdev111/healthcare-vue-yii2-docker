<?php

/**
 * @OA\Post (
 *     path="/patient/health-profile-health?healthProfileId={healthProfileId}",
 *     tags={"HealthProfileHealth"},
 *     summary="Create and update health profile health",
 *     description="
 *     All body params are optional except isAcceptAgreement property, it must be true
 *     In case objects that have update and add structure will be set to empty array, then all data related with it object will be deleted
 *     For skip deleting of objects values need just remove it key from request
 *     In case objects that have update and add structure will be set to any values, it will be add or update to database, another values will be deleted
 *     Objects that have update and add must be have an unique values in case saving identifier of objects, except text saving",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="Health profile id",
 *         in="path",
 *         name="healthProfileId",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\RequestBody(
 *        @OA\MediaType(
 *            mediaType="application/json",
 *            @OA\Schema(
 *                  type="object",
 *                  @OA\Property(
 *                      property="isAcceptAgreement",
 *                      description="Accept agreenment, always must be set to true",
 *                      type="boolean"
 *                  ),
 *                  @OA\Property(
 *                      property="smokeId",
 *                      description="Unique identifier of HealthSmokeModel",
 *                      type="integer"
 *                  ),
 *                  @OA\Property(
 *                      property="drinkId",
 *                      description="Unique identifier of HealthDrinkModel",
 *                      type="integer"
 *                  ),
 *                  @OA\Property(
 *                      property="isOtherSubstance",
 *                      description="Has other substance or no",
 *                      type="boolean"
 *                  ),
 *                  @OA\Property(
 *                      property="symptoms",
 *                      description="Must contains sympotoms ids",
 *                      ref="#/components/schemas/addUpdateListModel",
 *                  ),
 *                  @OA\Property(
 *                      property="medicalConditions",
 *                      description="Must contains medicalConditions ids",
 *                      ref="#/components/schemas/addUpdateListModel",
 *                  ),
 *                  @OA\Property(
 *                      property="autoImmuneDiseases",
 *                      description="Must contains autoImmuneDiseases ids",
 *                      ref="#/components/schemas/addUpdateListModel",
 *                  ),
 *                  @OA\Property(
 *                      property="allergies",
 *                      description="Must contains allergies ids",
 *                      ref="#/components/schemas/addUpdateListModel",
 *                  ),
 *                  @OA\Property(
 *                      property="allergiesCategory",
 *                      description="Must contains allergies category ids",
 *                      ref="#/components/schemas/addUpdateListModel",
 *                  ),
 *                  @OA\Property(
 *                      property="foodIntolerances",
 *                      description="Must contains foodIntolerances ids",
 *                      ref="#/components/schemas/addUpdateListModel",
 *                  ),
 *                  @OA\Property(
 *                      property="foodIntolerancesCategory",
 *                      description="Must contains allergies category ids",
 *                      ref="#/components/schemas/addUpdateListModel",
 *                  ),
 *                  @OA\Property(
 *                      property="lifestyleDiet",
 *                      description="Must contains lifestyleDiet ids",
 *                      ref="#/components/schemas/addUpdateListModel",
 *                  ),
 *                  @OA\Property(
 *                      property="currentMedications",
 *                      description="Must contains currentMedications text",
 *                      ref="#/components/schemas/addUpdateListModel",
 *                  ),
 *                  @OA\Property(
 *                      property="healthConcerns",
 *                      description="Must contains healthConcerns text",
 *                      ref="#/components/schemas/addUpdateListModel",
 *                  ),
 *                  @OA\Property(
 *                      property="healthGoals",
 *                      description="Must contains healthGoals ids",
 *                      ref="#/components/schemas/addUpdateListModel",
 *                  ),
 *                  @OA\Property(
 *                      property="other",
 *                      description="Must contains other text",
 *                      ref="#/components/schemas/addUpdateListModel",
 *                  ),
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

<?php

/**
 * @OA\Get(
 *     path="/notification/settings",
 *     tags={"Notifications settings"},
 *     summary="Get list of notifications settings",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Response(
 *         response="200",
 *         description="Successful result ",
 *         @OA\JsonContent(ref="#/components/schemas/NotificationSettingResponse")
 *     )
 * )
 */

/**
 * @OA\Post  (
 *     path="/notification/settings",
 *     tags={"Notifications settings"},
 *     summary="Set notification settings",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\RequestBody(
 *            @OA\MediaType (
 *              mediaType="application/json",
 *               @OA\Schema(
 *                      type="object",
 *                      @OA\Property(
 *                          property="notificationTypes",
 *                          description="List ids of notifications settings",
 *                          type="array",
 *                          @OA\Items(
 *                              type="integer"
 *                          ),
 *                      )
 *                )
 *           )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success. Data is empty",
 *         @OA\JsonContent(ref="#/components/schemas/DefaultResponse")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation errors",
 *         @OA\Schema(ref="#/components/schemas/ValidationError"),
 *     )
 * )
 */

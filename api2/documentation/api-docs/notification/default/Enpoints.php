<?php

/**
 * @OA\Get(
 *     path="/notifications",
 *     tags={"Notifications"},
 *     summary="Get list of notifications",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Response(
 *         response="200",
 *         description="Successful result ",
 *         @OA\JsonContent(ref="#/components/schemas/NotificationResponse")
 *     )
 * )
 */

/**
 * @OA\Put (
 *     path="/notifications/read/{notificationId}",
 *     tags={"Notifications"},
 *     summary="Mark notification as read",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="notification ID",
 *         in="path",
 *         name="notificationId",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success. Data is empty",
 *         @OA\JsonContent(ref="#/components/schemas/DefaultResponse")
 *     ),
 *     @OA\Response(response="400", description="Invalid notification data.")
 * )
 */

/**
 * @OA\Get (
 *     path="/notifications/unread-count",
 *     tags={"Notifications"},
 *     summary="Get count of unread notifications",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Response(
 *         response="200",
 *         description="Successful result",
 *         @OA\JsonContent(ref="#/components/schemas/NotificationCountResponse")
 *     ),
 *     @OA\Response(response="400", description="Invalid notification data.")
 * )
 */

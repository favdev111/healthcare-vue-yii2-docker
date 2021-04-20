<?php

/**
 * @OA\Schema(
 *   schema="NotificationModel",
 *   @OA\Property(
 *      property="id",
 *      type="integer",
 *      example=3,
 *      description="Unique identificator"
 *   ),
 *   @OA\Property(
 *      property="createdAt",
 *      type="string",
 *      example="2020-12-03T12:41:50+00:00",
 *      description="Created at. Format: Y-m-d\TH:i:sP Compatible with ISO-8601"
 *   ),
 *   @OA\Property(
 *      property="message",
 *      type="string",
 *      example="Invalid card number",
 *      description="Message"
 *   ),
 *   @OA\Property(
 *      property="type",
 *      type="integer",
 *      example=1,
 *      description="Type of notifications. Types: 1=credit card"
 *   ),
 *   @OA\Property(
 *      property="isRead",
 *      type="boolean",
 *      example=true,
 *      description="Mark about read/unread notification"
 *   )
 * )
 */

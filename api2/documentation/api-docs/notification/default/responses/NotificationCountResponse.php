<?php

/**
 * @OA\Schema(
 *   schema="NotificationCountResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              @OA\Property(
 *                  property="count",
 *                  type="integer",
 *                  description="Count unread notifications",
 *                  example=5
 *              )
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

<?php

/**
 * @OA\Schema(
 *   schema="NotificationSettingResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/NotificationSettingModel")
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

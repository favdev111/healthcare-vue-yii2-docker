<?php

/**
 * @OA\Schema(
 *   schema="HealthSmokeResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/HealthSmokeModel")
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

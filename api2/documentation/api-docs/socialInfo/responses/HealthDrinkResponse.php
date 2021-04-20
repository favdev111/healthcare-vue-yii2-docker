<?php

/**
 * @OA\Schema(
 *   schema="HealthDrinkResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/HealthDrinkModel")
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

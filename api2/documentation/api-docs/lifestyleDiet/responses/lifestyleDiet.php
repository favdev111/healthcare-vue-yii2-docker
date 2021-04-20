<?php

/**
 * @OA\Schema(
 *   schema="LifestyleDietResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/LifestyleDietModel")
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

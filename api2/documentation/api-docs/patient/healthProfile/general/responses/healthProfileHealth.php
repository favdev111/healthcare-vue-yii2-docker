<?php

/**
 * @OA\Schema(
 *   schema="HealthProfileGeneralResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              ref="#/components/schemas/HealthProfileGeneralModel",
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

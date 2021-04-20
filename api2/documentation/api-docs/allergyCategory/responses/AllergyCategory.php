<?php

/**
 * @OA\Schema(
 *   schema="AllergyCategoryResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/AllergyCategoryModel")
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

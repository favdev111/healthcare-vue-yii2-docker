<?php

/**
 * @OA\Schema(
 *   schema="HealthTestCategoryModelResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              ref="#/components/schemas/HealthTestCategoryModel",
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

/**
 * @OA\Get(
 *     path="/health-test-category",
 *     tags={"health-test-category"},
 *     summary="Get  list",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="Health test category name",
 *         in="query",
 *         name="name",
 *         required=false,
 *         @OA\Schema(
 *           type="string",
 *         ),
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="List of health tests",
 *        @OA\Schema(ref="#/components/schemas/HealthTestModelResponse"),
 *    ),
 * )
 */

<?php

/**
 * @OA\Schema(
 *   schema="HealthTestModelResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              ref="#/components/schemas/HealthTestModel",
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

/**
 * @OA\Get(
 *     path="/health-test",
 *     tags={"health-test"},
 *     summary="Get list",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="Health test name",
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

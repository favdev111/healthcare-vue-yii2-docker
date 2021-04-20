<?php

/**
 * @OA\Schema(
 *   schema="HealthGoalResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              ref="#/components/schemas/AutoimmuneDiseaseModel",
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

/**
 * @OA\Get(
 *     path="/health-goal",
 *     tags={"health-goal"},
 *     summary="Get list",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="name",
 *         in="query",
 *         name="name",
 *         required=false,
 *         @OA\Schema(
 *           type="string",
 *         )
 *     ),
 *     @OA\Parameter(
 *         description="Page number",
 *         in="query",
 *         name="page",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *         ),
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="List",
 *        @OA\Schema(ref="#/components/schemas/HealthGoalResponse"),
 *    ),
 * )
 */

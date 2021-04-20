<?php

/**
 * @OA\Schema(
 *   schema="StateModelResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              ref="#/components/schemas/StateModel",
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

/**
 * @OA\Get(
 *     path="/state",
 *     tags={"State"},
 *     summary="Get state list",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="State name",
 *         in="query",
 *         name="name",
 *         required=false,
 *         @OA\Schema(
 *           type="string",
 *         ),
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="List of statess",
 *        @OA\Schema(ref="#/components/schemas/StateModelResponse"),
 *    ),
 * )
 */

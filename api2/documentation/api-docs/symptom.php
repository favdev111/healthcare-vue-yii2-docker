<?php

/**
 * @OA\Get(
 *     path="/symptom",
 *     tags={"symptom"},
 *     summary="Get symptom list",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="Symptom name",
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
 *        description="List of symptoms",
 *        @OA\Schema(ref="#/components/schemas/SymptomResponse"),
 *    ),
 * )
 */

/**
 * @OA\Schema(
 *   schema="SymptomResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              ref="#/components/schemas/SymptomModel",
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

<?php

/**
 * @OA\Schema(
 *   schema="EducationCollegeModelResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              ref="#/components/schemas/EducationCollegeModel",
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

/**
 * @OA\Get(
 *     path="/education-college",
 *     tags={"education-college"},
 *     summary="Get college list",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="College name",
 *         in="query",
 *         name="name",
 *         required=false,
 *         @OA\Schema(
 *           type="string",
 *         ),
 *     ),
 *     @OA\Parameter(
 *         description="Country code (1 - USA, 2 - Canada)",
 *         in="query",
 *         name="country",
 *         required=false,
 *         @OA\Schema(
 *           type="integer",
 *         ),
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="List of colleges",
 *        @OA\Schema(ref="#/components/schemas/EducationCollegeModelResponse"),
 *    ),
 * )
 */

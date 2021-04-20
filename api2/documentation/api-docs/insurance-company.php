<?php

/**
 * @OA\Schema(
 *   schema="InsuranceCompanyModelResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              ref="#/components/schemas/InsuranceCompanyModel",
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

/**
 * @OA\Get(path="/insurance-company",
 *     tags={"insurance-company"},
 *     summary="Get list of insurance companies",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="Company name",
 *         in="query",
 *         name="name",
 *         required=false,
 *         @OA\Schema(
 *           type="string",
 *         ),
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="List of insurance companies",
 *        @OA\Schema(ref="#/components/schemas/InsuranceCompanyModelResponse"),
 *    ),
 * )
 */

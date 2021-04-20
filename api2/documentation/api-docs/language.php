<?php

/**
 * @OA\Schema(
 *   schema="LanguageModelResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              ref="#/components/schemas/LanguageModel",
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

/**
 * @OA\Get(path="/language",
 *     tags={"language"},
 *     summary="Get state list",
 *     description="",
 *     security={{"Bearer":{}}},
 *    @OA\Response(
 *        response="200",
 *        description="List of statess",
 *        @OA\Schema(ref="#/components/schemas/LanguageModelResponse"),
 *    ),
 * )
 */

<?php

/**
 * @OA\Schema(
 *   schema="StaticPageModelResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              ref="#/components/schemas/StaticPageModel",
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

/**
 * @OA\Get(
 *     path="/static-page",
 *     tags={"Static page"},
 *     summary="Get static pages list",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="Page name",
 *         in="query",
 *         name="name",
 *         required=false,
 *         @OA\Schema(
 *           type="string",
 *         ),
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="List of pages",
 *        @OA\Schema(ref="#/components/schemas/StaticPageModelResponse"),
 *    ),
 * )
 */

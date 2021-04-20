<?php

/**
 * @OA\Get(
 *     path="/allergy-category",
 *     tags={"Allergy category"},
 *     summary="Get list of allergy categories names",
 *     @OA\Response(
 *         response="200",
 *         description="Successful result",
 *         @OA\JsonContent(ref="#/components/schemas/AllergyCategoryResponse")
 *     )
 * )
 */

/**
 * @OA\Get(
 *     path="/allergy-category/get-all",
 *     tags={"Allergy category"},
 *     summary="Get list of allergy categories and allergies. Endpoint special for health tab",
 *     @OA\Response(
 *         response="200",
 *         description="Successful result",
 *         @OA\JsonContent(ref="#/components/schemas/AllergyCategoryResponseAll")
 *     )
 * )
 */

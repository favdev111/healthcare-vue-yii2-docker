<?php

/**
 * @OA\Get(
 *     path="/allergy",
 *     tags={"Allergy"},
 *     summary="Get list of allergy names",
 *     @OA\Response(
 *         response="200",
 *         description="Successful result",
 *         @OA\JsonContent(ref="#/components/schemas/AllergyResponse")
 *     )
 * )
 */

<?php

/**
 * @OA\Get(
 *     path="/list-patient",
 *     tags={"list-patient"},
 *     security={{"Bearer":{}}},
 *     summary="Get patient list",
 *     description="",
 *    @OA\Response(
 *        response="200",
 *        description="List of patient",
 *        @OA\Schema(ref="#/components/schemas/ListPatientModelResponse"),
 *    ),
 *    @OA\Response(
 *        response=422,
 *        description="Validation errors",
 *        @OA\Schema(ref="#/components/schemas/ValidationError"),
 *    ),
 * )
 */

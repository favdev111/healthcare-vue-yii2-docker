<?php

/**
 * @OA\Patch (
 *     path="/accounts/password",
 *     tags={"accounts"},
 *     summary="Change password of current auth account",
 *     @OA\RequestBody(
 *            @OA\MediaType (
 *              mediaType="application/json",
 *               @OA\Schema(
 *                      type="object",
 *                      @OA\Property(
 *                          property="passwordCurrent",
 *                          description="Password of current auth account",
 *                          type="string",
 *                          example="oldSuperP@ssw0rd"
 *                      ),
 *                      @OA\Property(
 *                          property="password",
 *                          description="New password",
 *                          type="string",
 *                          example="newSuperP@ssw0rd"
 *                      )
 *                )
 *           )
 *     ),
 *     @OA\Response(
 *         response="204",
 *         description="Success. Response is empty."
 *     )
 * )
 */

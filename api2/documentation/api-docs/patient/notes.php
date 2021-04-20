<?php

/**
 * /**
 * @OA\Post(path="/patient/notes",
 *     tags={"notes"},
 *     summary="Submit patient notes",
 *     security={{"Bearer":{}}},
 *     description="",
 *     @OA\RequestBody(
 *        @OA\MediaType (
 *            mediaType="application/json",
 *            @OA\Schema(
 *            type="object",
 *            @OA\Property(
 *                property="accountId",
 *                description="accountId",
 *                type="integer"
 *            ),
 *            @OA\Property(
 *                property="createdBy",
 *                description="createdBy",
 *                type="integer"
 *            ),
 *            @OA\Property(
 *                property="content",
 *                type="string"
 *            ),
 *             @OA\Property(
 *                property="createdAt",
 *                description="In format Y-m-d h:i:s",
 *                type="string"
 *            ),
 *         )
 *        )
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="Successful result"
 *    ),
 * )
 */

 /**
 * @OA\Get(
 *     path="/patient/notes/{accountId}",
 *     tags={"notes"},
 *     summary="Get patient notes",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="Account Id",
 *         in="path",
 *         name="accountId",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="List of notes"
 *    ),
 * )
 */

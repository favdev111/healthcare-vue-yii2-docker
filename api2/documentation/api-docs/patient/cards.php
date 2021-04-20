<?php

/**
 * @OA\Get(
 *     path="/patient/payment/card",
 *     tags={"patient", "cards"},
 *     summary="Get list of cards",
 *     description="",
 *     security={{"Bearer":{}}},
 *    @OA\Response(
 *        response="200",
 *        description="List of cards"
 *    ),
 * )
 */

/**
 * /**
 * @OA\Post(path="/patient/payment/card",
 *     tags={"patient", "cards"},
 *     summary="Add cards to accounts",
 *     security={{"Bearer":{}}},
 *     description="",
 *     @OA\RequestBody(
 *        @OA\MediaType (
 *            mediaType="application/json",
 *            @OA\Schema(
 *            type="object",
 *            @OA\Property(
 *                property="activePaymentCard",
 *                description="Token of active card",
 *               type="string"
 *            ),
 *            @OA\Property(
 *              description="Array of payment card token from stripe",
 *              property="paymentCardTokens",
 *              type="array",
 *              items={ "type":"string" },
 *              ),
 *         )
 *        )
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="Cards were added"
 *    ),
 *    @OA\Response(
 *        response=422,
 *        description="Validation errors",
 *        @OA\Schema(ref="#/components/schemas/ValidationError"),
 *    )
 * )
 */


/**
 * /**
 * @OA\Post(path="/patient/payment/card/set-active",
 *     tags={"patient", "cards"},
 *     summary="Set active card",
 *     security={{"Bearer":{}}},
 *     description="",
 *     @OA\RequestBody(
 *        @OA\MediaType (
 *            mediaType="application/json",
 *            @OA\Schema(
 *            type="object",
 *            @OA\Property(
 *                property="id",
 *                description="Card id",
 *               type="integer"
 *            ),
 *         )
 *        )
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="Cards was set as active",
 *    ),
 *    @OA\Response(
 *        response=404,
 *        description="Card not found",
 *    )
 * )
 */


/**
 * /**
 * @OA\Delete(path="/patient/payment/card/{id}",
 *     tags={"patient", "cards"},
 *     summary="Delete card",
 *     security={{"Bearer":{}}},
 *     description="",
 *     @OA\Parameter(
 *         description="Card id",
 *         in="path",
 *         name="id",
 *         required=true,
 *     ),
 *    @OA\Response(
 *        response="204",
 *        description="Cards was deleted",
 *    ),
 *    @OA\Response(
 *        response=404,
 *        description="Card not found",
 *    )
 * )
 */

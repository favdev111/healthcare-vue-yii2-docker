<?php

/**
 * /**
 * @OA\Post(path="/patient/accounts/signup",
 *     tags={"accounts", "patient"},
 *     summary="SignUp",
 *     description="",
 *     @OA\RequestBody(
 *        @OA\MediaType (
 *            mediaType="application/json",
 *            @OA\Schema(
 *            type="object",
 *            @OA\Property(
 *                property="email",
 *                description="Email address",
 *                type="string"
 *            ),
 *            @OA\Property(
 *                property="newPassword",
 *                description="Password",
 *                type="string"
 *            ),
 *            @OA\Property(
 *                property="firstName",
 *                description="First name",
 *               type="string"
 *            ),
 *            @OA\Property(
 *                property="lastName",
 *                description="Last name",
 *               type="string"
 *            ),
 *         )
 *        )
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="Sign up successful",
 *        @OA\Schema(ref="#/components/schemas/AccountModelResponse"),
 *    ),
 *    @OA\Response(
 *        response=422,
 *        description="Validation errors",
 *        @OA\Schema(ref="#/components/schemas/ValidationError"),
 *    )
 * )
 */

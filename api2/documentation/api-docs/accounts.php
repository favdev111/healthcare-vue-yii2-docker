<?php

/**
 * @OA\Post(path="/accounts/signin",
 *     tags={"accounts"},
 *     summary="Signin",
 *     description="",
 *     @OA\RequestBody(
 *            @OA\MediaType (
 *              mediaType="application/json",
 *               @OA\Schema(
 *                      type="object",
 *                      @OA\Property(
 *                          property="email",
 *                          description="Email address",
 *                          type="string"
 *                      ),
 *                      @OA\Property(
 *                          property="password",
 *                          description="Password",
 *                          type="string"
 *                      )
 *                )
 *           )
 *     ),
 *     @OA\Parameter(
 *         description="To return account extra data, for example, you can add 'chat', 'subjects'",
 *         in="query",
 *         name="expand",
 *         required=false,
 *         @OA\Schema(
 *             type="array",
 *             @OA\Items(type="string"),
 *         ),
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="Login successful",
 *        @OA\Schema(ref="#/components/schemas/AccountModel"),
 *    ),
 *    @OA\Response(
 *        response=422,
 *        description="Validation errors",
 *        @OA\Schema(ref="#/components/schemas/ValidationError"),
 *    )
 * )
 */

/**
 * @OA\Post(path="/accounts/signout",
 *    tags={"accounts"},
 *    summary="Sign out endpoint. Use to remove account authorization token.",
 *    description="",
 *    security={{"Bearer":{}}},
 *    @OA\Response(response="204", description="Authorization token successfully removed")
 * )
 */

/**
 * @OA\Get(path="/accounts/me",
 *     tags={"accounts"},
 *     summary="Get my account data",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="To return account extra data, for example, you can add 'chat', 'subjects'",
 *         in="query",
 *         name="expand",
 *         required=false,
 *         @OA\Schema(
 *             type="array",
 *             @OA\Items(type="string"),
 *         ),
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="Login successful",
 *        @OA\Schema(ref="#/components/schemas/AccountModelResponse"),
 *    ),
 * )
 */

/**
 * @OA\Post(path="/accounts/resend-confirmation",
 *     tags={"accounts"},
 *     summary="Resend confirmation",
 *     description="Resend confirmation message",
 *     *     @OA\RequestBody(
 *        @OA\MediaType (
 *            mediaType="application/json",
 *            @OA\Schema(
 *            type="object",
 *            @OA\Property(
 *                property="email",
 *                description="Email address",
 *                type="string"
 *            ),
 *         )
 *        )
 *     ),
 *    @OA\Response(
 *        response="204",
 *        description="Email was send"
 *    ),
 *    @OA\Response(
 *        response=404,
 *        description="No such unapproved account",
 *    )
 * )
 */

/**
 * @OA\Post(path="/accounts/signup",
 *     tags={"accounts"},
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
 *            @OA\Property(
 *                property="phoneNumber",
 *                description="Phone number",
 *                type="string"
 *            ),
 *            @OA\Property(
 *                property="dateOfBirth",
 *                description="Date of birth",
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

/**
 * @OA\Get(path="/constants",
 *     tags={"constants"},
 *     summary="Get constants",
 *     description="",
 *     @OA\Response(
 *         response="200",
 *         description="",
 *         @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *     )
 * )
 */

/**
 * @OA\Post(path="/accounts/confirm",
 *    tags={"accounts"},
 *    summary="Confirm account",
 *    description="",
 *    @OA\Response(response="200", description="")
 * )
 */

/**
 * @OA\Schema(
 *   schema="ConfigsResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              type="object",
 *              @OA\Property(
 *                  property="chat",
 *                  type="object",
 *                  description="Quickblox Chat Config",
 *                  @OA\Property(
 *                      property="appId",
 *                      type="integer",
 *                      description="Application Identifier"
 *                  ),
 *                  @OA\Property(
 *                      property="authKey",
 *                      type="string",
 *                      description="Authentication Key"
 *                  ),
 *                  @OA\Property(
 *                      property="authSecret",
 *                      type="string",
 *                      description="Authentication Secret"
 *                  )
 *              ),
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

/**
 * @OA\Get(path="/configs",
 *     tags={"configs"},
 *     summary="Get config. For example config for chat (appId, authKey, authSecret)",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Response(
 *         response="200",
 *         description="Login successful",
 *         @OA\Schema(ref="#/components/schemas/ConfigsResponse"),
 *     )
 * )
 */

/**
 * @OA\Get(
 *     path="/accounts/subjects",
 *     tags={"accounts"},
 *     summary="Get subjects",
 *     description="",
 *     security={{"Bearer":{}}},
 *     @OA\Parameter(
 *         description="Job ID",
 *         in="query",
 *         name="jobId",
 *         required=false,
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="",
 *         @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *     )
 * )
 */

/**
 * @OA\Post(path="/accounts/new-device-token",
 *    tags={"accounts"},
 *    summary="Change Device token",
 *    description="",
 *    security={{"Bearer":{}}},
 *    @OA\RequestBody(
 *         required=true,
 *         @OA\Schema(
 *            type="object",
 *            @OA\Property(
 *                property="token",
 *                type="string",
 *                description="New Device token"
 *            )
 *         )
 *     ),
 *    @OA\Response(response="200", description="")
 * )
 */

/**
 * @OA\Schema(
 *   schema="AccountModel",
 *   @OA\Property(
 *      property="email",
 *      type="string",
 *      description="Email",
 *      example="test@gmail.com"
 *   ),
 *   @OA\Property(
 *      property="newPassword",
 *      type="string",
 *      description="Password"
 *   ),
 * )
 */

/**
 * @OA\Schema(
 *   schema="AccountModelWithTokenResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              type="object",
 *              allOf={
 *                  @OA\Schema(ref="#/components/schemas/AccountModel"),
 *                  @OA\Schema(
 *                      @OA\Property(
 *                          property="accessToken",
 *                          type="string",
 *                          description="Access Token"
 *                      ),
 *                  ),
 *              },
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

/**
 * @OA\Schema(
 *   schema="AccountModelResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              ref="#/components/schemas/AccountModel",
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

/**
 * @OA\Put(path="/accounts",
 *     tags={"accounts"},
 *     summary="Update auth account",
 *     security={{"Bearer":{}}},
 *     @OA\RequestBody(
 *        @OA\MediaType (
 *            mediaType="application/json",
 *            @OA\Schema(
 *            type="object",
 *            required={"gender", "firstName", "lastName", "phoneNumber", "dateOfBirth"},
 *            @OA\Property(
 *                property="gender",
 *                description="Gender ID",
 *                type="string",
 *                example="M"
 *            ),
 *            @OA\Property(
 *                property="firstName",
 *                description="First name",
 *                type="string",
 *                example="John"
 *            ),
 *            @OA\Property(
 *                property="lastName",
 *                description="Last name",
 *                type="string",
 *                example="Doe"
 *            ),
 *            @OA\Property(
 *                property="phoneNumber",
 *                description="Phone number",
 *                type="string",
 *                example="8888888888"
 *            ),
 *            @OA\Property(
 *                property="dateOfBirth",
 *                description="Date of birth",
 *                type="string",
 *                example="1991-12-30"
 *            ),
 *          )
 *        )
 *     ),
 *    @OA\Response(
 *        response="200",
 *        description="Update successful",
 *        @OA\JsonContent(ref="#/components/schemas/AccountModelResponse")
 *    ),
 *    @OA\Response(
 *        response=422,
 *        description="Validation errors",
 *        @OA\Schema(ref="#/components/schemas/ValidationError"),
 *    )
 * )
 */

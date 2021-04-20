<?php

/**
 * @OA\OpenApi(
 *   openapi="3.0.0",
 * )
 */

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="Winitclinic API v1",
 *   @OA\Contact(
 *     email="hello@eltexsoft.com"
 *   )
 * )
 */

/**
 * @OA\Server(
 *   url="/api/v1",
 *   @OA\ServerVariable(
 *     serverVariable="schema",
 *     enum={"https", "http"},
 *     default=""
 *   )
 * )
 */

/**
 * @OA\Parameter(
 *   parameter="page",
 *   name="page",
 *   in="query",
 *   description="Page number (default value is 1)",
 *   required=false,
 *   @OA\Schema(
 *     type="integer",
 *   ),
 * )
 */

/**
 * @OA\Parameter(
 *   parameter="per-page",
 *   name="per-page",
 *   in="query",
 *   description="Items per page (default value is 20)",
 *   required=false,
 *   @OA\Schema(
 *     type="integer",
 *   ),
 * )
 */

/**
 * @OA\SecurityScheme(
 *   securityScheme="Bearer",
 *   type="apiKey",
 *   name="Authorization",
 *   in="header",
 * )
 */

/**
 * @OA\Schema(
 *   schema="DefaultResponse",
 *   @OA\Property(
 *     property="success",
 *     description="Whether request was successful or not (false for 422, true for 200, etc.)",
 *     type="boolean",
 *   ),
 *   @OA\Property(
 *     property="statusCode",
 *     description="HTTP Response status code",
 *     type="integer",
 *   ),
 *   @OA\Property(
 *     property="message",
 *     description="Response description",
 *     type="string",
 *   ),
 * )
 */

/**
 * @OA\Schema(
 *   schema="UnauthorizationError",
 *   type="object",
 *   allOf={
 *     @OA\Schema(
 *       ref="#/components/schemas/DefaultResponse",
 *     ),
 *     @OA\Schema(
 *       @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(
 *         ),
 *       ),
 *     ),
 *   },
 * )
 */

/**
 * @OA\Schema(
 *   schema="ValidationError",
 *   allOf={
 *     @OA\Schema(
 *       ref="#/components/schemas/ValidationErrorData",
 *     ),
 *     @OA\Schema(
 *       ref="#/components/schemas/DefaultResponse",
 *     ),
 *   },
 * )
 */

/**
 * @OA\Schema(
 *   schema="ValidationErrorData",
 *   @OA\Property(
 *     property="data",
 *     type="array",
 *     @OA\Items(
 *       description="List of errors",
 *       type="object",
 *       @OA\Property(
 *         property="field",
 *         description="Attribute name",
 *         type="string",
 *       ),
 *       @OA\Property(
 *         property="message",
 *         description="Error message text",
 *         type="string",
 *       ),
 *     ),
 *   ),
 * )
 */

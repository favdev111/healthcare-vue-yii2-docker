<?php

/**
 * @OA\Schema(
 *   schema="addUpdateListModel",
 *   @OA\Property(
 *       property="add",
 *       @OA\Items(anyOf={@OA\Schema(type="string")})
 *   ),
 *   @OA\Property(
 *      property="update",
 *      type="array",
 *      @OA\Items(
 *          required={"id", "value"},
 *          @OA\Property(
 *              property="id",
 *              type="integer",
 *              description="Unique identificator",
 *              example=1
 *          ),
 *          @OA\Property(
 *              property="value",
 *              type="string",
 *              description="value",
 *              example="String or integer values"
 *          )
 *      )
 *    )
 * )
 */

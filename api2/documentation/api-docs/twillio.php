<?php

/**
 * @OA\Post(
 *     path="/twillio/roomcreate",
 *     tags={"Twillio video call"},
 *     summary="Create room for video call",
 *     description="",
 *     @OA\Parameter(
 *        description="client name",
 *        in="query",
 *        name="client_name",
 *        required=false,
 *        @OA\Schema(
 *          type="string",
 *        )
 *    ),
 *    @OA\Parameter(
 *        description="patient id",
 *        in="query",
 *         name="patient_id",
 *        required=false,
 *        @OA\Schema(
 *          type="integer",
 *        )
 *    ),
  *    @OA\Parameter(
 *        description="doctor id",
 *        in="query",
 *         name="doctor_id",
 *        required=false,
 *        @OA\Schema(
 *          type="integer",
 *        )
 *    ),
 *    @OA\Response(
 *        response="200",
 *        description="Room create successfully",
 *    ),
 * )
 */

<?php

/**
 * @OA\Get(
 *     path="/lifestyle-diet",
 *     tags={"Lifestyle diet"},
 *     summary="Get list of lifestyle diet names",
 *     @OA\Response(
 *         response="200",
 *         description="Successful result",
 *         @OA\JsonContent(ref="#/components/schemas/LifestyleDietResponse")
 *     )
 * )
 */

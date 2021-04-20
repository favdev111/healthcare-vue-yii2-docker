<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *   required={"accountId", "autoimmuneDiseaseId"},
 *   schema="AccountAutoimmuneDiseaseModel",
 *    @OA\Property(
 *      property="autoimmuneDiseaseId",
 *      type="integer",
 *      description=""
 *   ),
 * )
 */
class AccountAutoimmuneDisease extends \modules\account\models\ar\AccountAutoimmuneDisease
{

}

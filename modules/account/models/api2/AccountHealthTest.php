<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *   required={"accountId", "healthTestId"},
 *   schema="AccountHealthTestModel",
 *    @OA\Property(
 *      property="healthTestId",
 *      type="integer",
 *      description=""
 *   ),
 * )
 */
class AccountHealthTest extends \modules\account\models\ar\AccountHealthTest
{

}

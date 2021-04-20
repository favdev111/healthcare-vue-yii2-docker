<?php

namespace modules\account\models\api2;

use Yii;

/**
 * @OA\Schema(
 *   schema="AccountProfileSignUpModel",
 *   @OA\Property(
 *      property="firstName",
 *      type="string",
 *      description="First Name"
 *   ),
 *   @OA\Property(
 *      property="lastName",
 *      type="string",
 *      description="Last Name"
 *   ),
 *   @OA\Property(
 *      property="phoneNumber",
 *      type="string",
 *      description="phone number"
 *   ),
 *   @OA\Property(
 *      property="dateOfBirth",
 *      type="string",
 *      description="Date of Birth"
 *   )
 * )
 */


/**
 * @OA\Schema(
 *   schema="AccountProfileModel",
 *   @OA\Property(
 *      property="firstName",
 *      type="string",
 *      description="First Name"
 *   ),
 *   @OA\Property(
 *      property="lastName",
 *      type="string",
 *      description="Last Name"
 *   ),
 *   @OA\Property(
 *      property="phoneNumber",
 *      type="string",
 *      description="phone number"
 *   ),
 *   @OA\Property(
 *      property="dateOfBirth",
 *      type="string",
 *      description="Date of Birth"
 *   )
 * )
 */



/**
 * @inheritdoc
 */
class Profile extends \modules\account\models\Profile
{
    public function rules()
    {
        $rules = parent::rules();
        unset($rules['zipCodeRequired']);
        return array_merge($rules, [
            [['dateOfBirth'], 'date', 'format' => 'php: Y-m-d', 'min' => '1900-01-01'],
        ]);
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_SING_UP_SPECIALIST] = ['firstName', 'lastName', 'phoneNumber', 'dateOfBirth'];
        return $scenarios;
    }
}

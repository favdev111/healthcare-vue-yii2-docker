<?php

namespace modules\account\models\api2;

/**
 * @OA\Schema(
 *   schema="AccountRateModel",
 *   required={"ratePolicy", "rate15", "rate30", "rate45", "rate60"},
 *   @OA\Property(
 *       description="Accept rate policy",
 *       property="ratePolicy",
 *       type="boolean",
 *       default="true"
 *   ),
 *   @OA\Property(
 *      property="rate15",
 *      type="number",
 *      description="Rate fo 15 min"
 *   ),
 *   @OA\Property(
 *      property="rate30",
 *      type="number",
 *      description="Rate fo 30 min"
 *   ),
 *   @OA\Property(
 *      property="rate45",
 *      type="number",
 *      description="Rate fo 45 min"
 *   ),
 *   @OA\Property(
 *      property="rate60",
 *      type="number",
 *      description="Rate fo 60 min"
 *   ),
 * )
 */
class AccountRate extends \modules\account\models\ar\AccountRate
{
    public function fields()
    {
        return [
            'ratePolicy',
            'rate15',
            'rate30',
            'rate45',
            'rate60',
        ];
    }

    public function rules()
    {
        $module = \Yii::$app->getModuleAccount();
        return [
            ['hourlyRate', 'double', 'min' => $module->hourlyRateMin, 'max' => $module->hourlyRateMax]
        ];
    }
}

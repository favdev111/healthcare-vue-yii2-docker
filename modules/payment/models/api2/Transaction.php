<?php

namespace modules\payment\models\api2;

/**
 * @OA\Schema(
 *   schema="TransactionModel",
 *   @OA\Property(
 *      property="id",
 *      type="integer",
 *      description="Transaction ID"
 *   ),
 *   @OA\Property(
 *      property="statusTitle",
 *      type="string",
 *      description="Status Title"
 *   ),
 *   @OA\Property(
 *      property="amount",
 *      type="number",
 *      description="Amount"
 *   ),
 *   @OA\Property(
 *      property="fee",
 *      type="number",
 *      description="Fee"
 *   ),
 *   @OA\Property(
 *      property="allowRefund",
 *      type="boolean",
 *      description="Is Refund Allowed"
 *   )
 * )
 */

/**
 * @OA\Schema(
 *   schema="TransactionModelResponse",
 *   allOf={
 *      @OA\Schema(
 *          @OA\Property(
 *              property="data",
 *              ref="#/components/schemas/TransactionModel",
 *          ),
 *      ),
 *      @OA\Schema(ref="#/components/schemas/DefaultResponse")
 *   }
 * )
 */

/**
 * @inheritdoc
 */
class Transaction extends \modules\payment\models\Transaction
{
    public function fields()
    {
        $fields = [
            'id',
            'statusTitle' => 'statusText',
            'amount' => function () {
                return (float)$this->amount;
            },
            'fee' => function () {
                return (float)$this->fee;
            },
        ];

        return $fields;
    }

    public function extraFields()
    {
        $fields = [];
        $fields['lesson'] = function () {
            return $this->objectType == static::TYPE_LESSON ? $this->lesson : null;
        };
        return $fields;
    }
}

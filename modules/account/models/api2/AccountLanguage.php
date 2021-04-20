<?php

namespace modules\account\models\api2;

use modules\account\models\ar\AccountLanguage as Base;

/**
 * @OA\Schema(
 *  required={"languageId"},
 *   schema="AccountLanguageModel",
 *   @OA\Property(
 *      property="languageId",
 *      description="Language id",
 *      type="integer",
 *   ),
 * )
 */
class AccountLanguage extends Base
{
    public function rules()
    {
        return [
            [['languageId'], 'required'],
            [['languageId'], 'integer'],
            [
                ['languageId'],
                'exist',
                'targetClass' => \modules\account\models\ar\Language::class,
                'targetAttribute' => 'id',
            ],
        ];
    }
}

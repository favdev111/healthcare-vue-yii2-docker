<?php

namespace modules\account\models\api;

use modules\account\models\Account;
use modules\account\models\query\AccountQuery;

/**
 * @inheritdoc
 * @property-read Account $responsible
 */
class JobApply extends \modules\account\models\JobApply
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['accountId'], 'integer'],
            [
                ['accountId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Account::class,
                'targetAttribute' => ['accountId' => 'id'],
                'filter' => function ($query) {
                    /**
                     * @var $query AccountQuery
                     */
                    $query->tutor()->active();
                }
            ],
            [['isManual'], 'default', 'value' => true],
        ]);
    }
}

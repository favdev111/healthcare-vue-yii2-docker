<?php

namespace modules\account\models\api;

use common\components\HtmlPurifier;
use Yii;

/**
 * @inheritdoc
 */
class Profile extends \modules\account\models\Profile
{
    public static function rulesCommon()
    {
        return array_merge(
            parent::rulesCommon(),
            [
                [['companyName'], 'trim'],
                [['companyName'], 'filter', 'filter' => function ($value) {
                    return HtmlPurifier::process($value, ['HTML.Allowed' => '']);
                }
                ],
                [['companyName'], 'string', 'max' => 255],
                [['companyName'], 'unique', 'targetClass' => static::className(), 'targetAttribute' => ['companyName', 'taxId'], 'filter' => function ($query) {
                    if (!Yii::$app->user->isGuest) {
                        $query->andWhere(['<>', 'accountId', Yii::$app->user->id]);
                    }
                }
                ],
                [['taxId'], 'match', 'pattern' => '/^\d{9}$/'],
            ]
        );
    }

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['firstName', 'lastName', 'zipCode'], 'required'],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'firstName',
            'lastName',
            'phoneNumber',
            'address',
            'companyName',
            'mainPhoneNumberType',
            'taxId',
            'zipCode'
        ];
    }
}

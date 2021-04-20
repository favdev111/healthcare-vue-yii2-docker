<?php

namespace modules\payment\models\api;

use modules\payment\models\Account;
use Yii;

/**
 * @inheritdoc
 */
class PaymentAccount extends Account
{
    public function fields()
    {
        $fields = parent::fields();
        $fields['extraDataFields'] = function () {
            return PaymentInfo::getExtraVerifyDataFields();
        };
        unset($fields['verified']);
        // Verified field means only that billing address and last 4 digits of SSN are not required.
        // But still full SSN or Document may be required. See extraDataFields.
        // Renaming field to be more appropriate in API
        $fields['billingAddressVerified'] = 'verified';
        $fields['stripeAccountUpdateLink'] = function () {
            return $this->updatesRequired
                ? Yii::$app->payment->createConnectOnboadringLink($this->paymentAccountId, true)
                : null;
        };
        return $fields;
    }
}

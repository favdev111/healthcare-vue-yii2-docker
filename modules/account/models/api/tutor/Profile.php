<?php

namespace modules\account\models\api\tutor;

use common\components\HtmlPurifier;
use Yii;

/**
* @inheritdoc
 */
class Profile extends \modules\account\models\Profile
{
    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();
        $fields['cityName'] = 'cityName';
        return $fields;
    }
}

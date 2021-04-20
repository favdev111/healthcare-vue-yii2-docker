<?php

namespace modules\account\models\api\tutor;

/**
 * @inheritdoc
 */
class Subject extends \modules\account\models\Subject
{
    public function fields()
    {
        $fields = parent::fields();
        $fields['categories'] = 'categories';
        return $fields;
    }
}

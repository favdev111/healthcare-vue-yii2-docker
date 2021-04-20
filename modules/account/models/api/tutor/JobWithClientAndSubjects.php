<?php

namespace modules\account\models\api\tutor;

use common\helpers\Location;
use modules\payment\models\api\CardInfo;
use yii\base\NotSupportedException;
use yii\db\ActiveQueryInterface;

/**
 * @inheritdoc
 */
class JobWithClientAndSubjects extends \modules\account\models\api\Job
{
    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();
        $fields['name'] = 'nameWithLocationAndSubject';
        $fields['client'] = 'account';
        $fields['jobHires'] = 'jobHires';
        $fields['subjects'] = function () {
            $subjects = $this->getSubjects()->all();
            return $subjects;
        };
        return $fields;
    }
}

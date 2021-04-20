<?php

namespace common\models\query;

use common\models\Sms;

/**
 * This is the ActiveQuery class for [[\common\models\Sms]].
 *
 * @see \common\models\Sms
 */
class SmsQuery extends \yii\db\ActiveQuery
{
    public function followUpJobHiredTutor()
    {
        return $this->andWhere([Sms::tableName() . '.type' => Sms::TYPE_FOLLOW_UP_HIRE_TO_TUTOR]);
    }

    public function statusWaitingForSend()
    {
        return $this->byStatus(Sms::STATUS_WAITING_TO_SEND);
    }

    public function createdForLast24hours()
    {
        return $this->forLast24Hours('createdAt');
    }

    public function sentMoreThan24Ago()
    {
        $dateTime = new \DateTime();
        $dateTime = $dateTime->modify('-24 hours')->format(\Yii::$app->formatter->MYSQL_DATETIME);
        return $this->andWhere([
            '<',
            Sms::tableName() . '.sentAt',
            $dateTime
        ]);
    }

    public function bySid(string $value)
    {
        return $this->andWhere(['twilioMessageSID' => $value])->limit(1);
    }


    public function forLast24Hours($field)
    {
        $dateTime = new \DateTime();
        $dateTime = $dateTime->modify('-24 hours')->format(\Yii::$app->formatter->MYSQL_DATETIME);
        return $this->andWhere([
            '>=',
            Sms::tableName() . '.' . $field,
            $dateTime
        ]);
    }

    public function byStatus(int $status)
    {
        return $this->andWhere([Sms::tableName() . '.status' => $status]);
    }

    public function byPhone(string $phone)
    {
        return $this->andWhere(['like', Sms::tableName() . '.phone', $phone]);
    }
}

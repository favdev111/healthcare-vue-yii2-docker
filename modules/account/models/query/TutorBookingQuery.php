<?php

namespace modules\account\models\query;

/**
 * This is the ActiveQuery class for [[\modules\account\models\TutorBooking]].
 *
 * @see \modules\account\models\TutorBooking
 */
class TutorBookingQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \modules\account\models\TutorBooking[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \modules\account\models\TutorBooking|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}

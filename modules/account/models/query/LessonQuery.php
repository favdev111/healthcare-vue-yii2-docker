<?php

namespace modules\account\models\query;

use common\components\ActiveQuery;
use Yii;
use DateTime;

/**
 * Class LessonQuery
 * @package modules\account\models\query
 */
class LessonQuery extends ActiveQuery
{
    /**
     * @param $id
     * @return $this
     */
    public function ofStudent($id)
    {
        return $this->andWhere([$this->tableName . '.studentId' => $id]);
    }

    /**
     * @param $id
     * @return $this
     */
    public function ofTutor($id)
    {
        return $this->andWhere([$this->tableName . '.tutorId' => $id]);
    }

    /**
     * @param $ids
     * @return $this
     */
    public function bySubject(array $ids)
    {
        return $this->andWhere([$this->tableName . '.subjectId' => $ids]);
    }



    public function betweenEndLessonDates(DateTime $from, DateTime $to): self
    {
        $this->andWhere([
            'between',
            $this->tableName . '.toDate',
            $from->format(Yii::$app->formatter->MYSQL_DATE),
            $to->format(Yii::$app->formatter->MYSQL_DATE)
        ]);

        return $this;
    }

    public function betweenCreateLessonDates(DateTime $from, DateTime $to): self
    {
        $this->andWhere([
            'between',
            $this->tableName . '.createdAt',
            $from->format(Yii::$app->formatter->MYSQL_DATE),
            $to->format(Yii::$app->formatter->MYSQL_DATE)
        ]);

        return $this;
    }
}

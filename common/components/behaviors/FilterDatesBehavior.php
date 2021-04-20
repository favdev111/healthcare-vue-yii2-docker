<?php

namespace common\components\behaviors;

use common\components\Formatter;
use modules\account\helpers\Timezone;
use Yii;
use yii\base\Behavior;

class FilterDatesBehavior extends Behavior
{
    public $dateFrom;
    public $dateTo;
    public $incomingDateFormat = 'm/d/Y';

    public function getLessonFilterDateRules()
    {
        //TODO refactor this with getFilterDatesRulesArray()
        return [
            [['fromDate', 'toDate'], 'date', 'format' => 'php: Y-m-d', 'skipOnEmpty' => true],
            [['fromDate'], function ($attribute) {
                $from = \DateTime::createFromFormat('Y-m-d', $this->owner->fromDate);
                $to = \DateTime::createFromFormat('Y-m-d', $this->owner->toDate);
                if ($from > $to) {
                    $this->owner->addError($attribute, 'Incorrect date fields');
                }
            },'when' => function () {
                return !empty($this->toDate) && !empty($this->fromDate);
            },
            ],
            [['toDate'], function ($attribute) {
                $to = \DateTime::createFromFormat('Y-m-d 23:59:59', $this->owner->toDate);
                $now = new \DateTime();
                if ($to > $now) {
                    $this->owner->addError($attribute, 'Incorrect date fields');
                }
            }, 'skipOnEmpty' => true,
            ]
        ];
    }

    public function getFilterDatesRulesArray()
    {
        return [
            [['dateFrom', 'dateTo'], 'date', 'format' => 'php:' . $this->incomingDateFormat, 'skipOnEmpty' => true],
            [['dateFrom'], function ($attribute) {
                $from = \DateTime::createFromFormat($this->incomingDateFormat, $this->dateFrom);
                $to = \DateTime::createFromFormat($this->incomingDateFormat, $this->dateTo);
                if ($from > $to) {
                    $this->owner->addError($attribute, 'Incorrect date fields');
                }
            },'when' => function () {
                return !empty($this->dateTo) && !empty($this->dateFrom);
            },
            ],
            [['dateTo'], function ($attribute) {
                $to = \DateTime::createFromFormat('Y-m-d 23:59:59', $this->dateTo);
                $now = new \DateTime();
                if ($to > $now) {
                    $this->owner->addError($attribute, 'Incorrect date fields');
                }
            }, 'skipOnEmpty' => true,
            ]
        ];
    }

    public function filterDateHaving($query, $tableName, $withTime = true, string $field = 'createdAt')
    {
        return $this->filteringByDates($query, $tableName, $withTime, $field, true);
    }

    public function filterDate($query, $tableName, $withTime = true, string $field = 'createdAt')
    {
        return $this->filteringByDates($query, $tableName, $withTime, $field, false);
    }

    protected function filteringByDates(
        $query,
        $tableName,
        $withTime = true,
        string $field = 'createdAt',
        $isHaving = false
    ) {
        /**
         * @var $formatter Formatter
         */
        $formatter = Yii::$app->formatter;

        if (!empty($this->dateFrom)) {
            $dateTime = \DateTime::createFromFormat($this->incomingDateFormat, $this->dateFrom);
            $format = $formatter->MYSQL_DATE . ($withTime ? ' 00:00:00' : '');
            $dateFrom = $dateTime->format($format);
            if ($isHaving) {
                $query->andHaving([
                    '>=', (!empty($tableName) ? $tableName . '.' : '') . $field, $dateFrom,
                ]);
            } else {
                $query->andWhere([
                    '>=',(!empty($tableName) ? $tableName . '.' : '') . $field, $dateFrom,
                ]);
            }
        }

        if (!empty($this->dateTo)) {
            $dateTime = \DateTime::createFromFormat($this->incomingDateFormat, $this->dateTo);
            $format = $formatter->MYSQL_DATE . ($withTime ? ' 23:59:59' : '');
            $dateTo = $dateTime->format($format);
            if ($isHaving) {
                $query->andHaving([
                    '<=', (!empty($tableName) ? $tableName . '.' : '') . $field, $dateTo,
                ]);
            } else {
                $query->andWhere([
                    '<=', (!empty($tableName) ? $tableName . '.' : '') . $field, $dateTo,
                ]);
            }
        }
        return $query;
    }

    /**
     * @param $query
     * @param $fieldName
     * @param $tableName
     * @param string $fromFormat - format of incoming data
     */
    public function addDateLessonFilter($query, $fieldName, $tableName, $fromFormat = 'Y-m-d')
    {
        $format = 'Y-m-d H:i';
        $owner = $this->owner;
        $fieldValue = $owner->$fieldName;
        $date = \DateTime::createFromFormat($fromFormat, $fieldValue);
        if (empty($date)) {
            return;
        }
        switch ($fieldName) {
            case 'fromDate':
            case 'dateFrom':
                //field name in db
                $fieldName = 'fromDate';
                $symbol = '>';
                $compare = Timezone::staticConvertToServerTimeZone($date->setTime(0, 0, 0)->format($format), $format);
                break;
            case 'dateTo':
            case 'toDate':
                //field name in db
                $fieldName = 'toDate';
                $symbol = '<';
                $compare = Timezone::staticConvertToServerTimeZone($date->setTime(23, 59, 59)->format($format), $format);
                break;
            default:
                return;
                break;
        }


        if ($date instanceof \DateTime) {
            $query->andWhere([
                $symbol,
                ($tableName ? $tableName . '.' : '') . $fieldName,
                $compare
            ]);
        }
    }

    public function addDefaultDateRangeCondition()
    {
        if (!$this->dateFrom) {
            $this->fillDefaultFrom();
        }
        if (!$this->dateTo) {
            $this->fillDefaultTo();
        }
        return $this;
    }


    protected function fillDefaultFrom()
    {
        $this->dateFrom = date('m/d/Y', strtotime('-30 days'));
    }
    protected function fillDefaultTo()
    {
        $this->dateTo = date('m/d/Y');
    }
}

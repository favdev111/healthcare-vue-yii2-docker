<?php

namespace common\components;

/**
 * Class BusinessDaysCalculator
 * @see https://codereview.stackexchange.com/questions/51895/calculate-future-date-based-on-business-days
 */
class BusinessDaysCalculator
{

    const MONDAY    = 1;
    const TUESDAY   = 2;
    const WEDNESDAY = 3;
    const THURSDAY  = 4;
    const FRIDAY    = 5;
    const SATURDAY  = 6;
    const SUNDAY    = 7;

    /**
     * @param \DateTime   $startDate       Date to start calculations from
     * @param \DateTime[] $holidays        Array of holidays, holidays are no considered business days.
     * @param int[]      $nonBusinessDays Array of days of the week which are not business days.
     */
    public function __construct(\DateTime $startDate, array $holidays, array $nonBusinessDays)
    {
        $this->date = $startDate;
        $this->holidays = $holidays;
        $this->nonBusinessDays = $nonBusinessDays;
    }

    /**
     * Array of holidays dates
     * @return array
     */
    public static function getHolidays()
    {
        return [
            new \DateTime(date('Y') . "-01-01"),
            new \DateTime(date('Y') . "-01-02"),
            new \DateTime(date('Y') . "-01-16"),
            new \DateTime(date('Y') . "-05-29"),
            new \DateTime(date('Y') . "-07-04"),
            new \DateTime(date('Y') . "-09-04"),
            new \DateTime(date('Y') . "-11-10"),
            new \DateTime(date('Y') . "-11-23"),
            new \DateTime(date('Y') . "-12-25"),
        ];
    }

    /**
     * @param $howManyDays
     * @return $this
     */
    public function addBusinessDays($howManyDays)
    {
        $i = 0;
        while ($i < $howManyDays) {
            $this->date->modify("+1 day");
            if ($this->isBusinessDay($this->date)) {
                $i++;
            }
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return bool
     */
    private function isBusinessDay(\DateTime $date)
    {
        if (in_array((int)$date->format('N'), $this->nonBusinessDays)) {
            return false; //Date is a nonBusinessDay.
        }
        foreach ($this->holidays as $day) {
            if ($date->format('Y-m-d') == $day->format('Y-m-d')) {
                return false; //Date is a holiday.
            }
        }
        return true; //Date is a business day.
    }
}

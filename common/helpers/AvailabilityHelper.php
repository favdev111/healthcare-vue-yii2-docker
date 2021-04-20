<?php

namespace common\helpers;

class AvailabilityHelper
{
    public static $showModalSessionKey = 'showAvailabilityModel';

    const BITE_STRING_LENGTH = 21;

    public static $weekdays = [
        'Sunday' => 'Sun',
        'Monday' => 'Mon',
        'Tuesday' => 'Tue',
        'Wednesday' => 'Wed',
        'Thursday' => 'Thu',
        'Friday' => 'Fri',
        'Saturday' => 'Sat',
    ];

    public static $availabilityDataMobile = ['Morning' => ['Before 12 PM' => [1 => 'Sun', 2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri', 7 => 'Sat']],
        'Afternoon' => ['12 PM - 5 PM' => [8 => 'Sun', 9 => 'Mon', 10 => 'Tue', 11 => 'Wed', 12 => 'Thu', 13 => 'Fri', 14 => 'Sat']],
        'Evening' => ['After 5 PM' => [15 => 'Sun', 16 => 'Mon', 17 => 'Tue', 18 => 'Wed', 19 => 'Thu', 20 => 'Fri', 21 => 'Sat']]
    ];

    public static $availabilityData =  [
        'Morning' => ['Before 12 PM' => [1 => 'Sunday', 2 => 'Monday', 3 => 'Tuesday', 4 => 'Wednesday', 5 => 'Thursday', 6 => 'Friday', 7 => 'Saturday']],
        'Afternoon' => ['12 PM - 5 PM' => [8 => 'Sunday', 9 => 'Monday', 10 => 'Tuesday', 11 => 'Wednesday', 12 => 'Thursday', 13 => 'Friday', 14 => 'Saturday']],
        'Evening' => ['After 5 PM' => [15 => 'Sunday', 16 => 'Monday', 17 => 'Tuesday', 18 => 'Wednesday', 19 => 'Thursday', 20 => 'Friday', 21 => 'Saturday']]
    ];

    public static function convertWeekDay($day)
    {
        $weekdays = static::$weekdays;

        if (isset($weekdays[$day])) {
            return $weekdays[$day];
        }

        $weekdays = array_flip($weekdays);

        return (isset($weekdays[$day])) ? $weekdays[$day] : false;
    }

    public static function mobileData($availabilityArray)
    {
        $arrayDays = [];

        $availability = static::$availabilityDataMobile;

        foreach ($availability as $dayTime => $times) {
            foreach ($times as $time) {
                foreach ($time as $key => $day) {
                    $arrayDays[$day][$dayTime] = (key_exists($key, $availabilityArray)) ? true : false;
                    $arrayDays[$day][$dayTime . '_key'] = $key;
                }
            }
        }

        return $arrayDays;
    }
}

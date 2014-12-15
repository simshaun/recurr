<?php

return array(
    'Unable to fully convert this rrule to text.' => 'Unable to fully convert this rrule to text.',
    'for %count% times' => 'for %count% times',
    'for %count% time' => 'for %count% time',
    '(~ approximate)' => '(~ approximate)',
    'until %date%' => 'until %date%', // e.g. every year until July 4, 2014
    'day_date' => defined('PHP_WINDOWS_VERSION_BUILD') ? '%B %#d, %Y' : '%B %e, %Y',
    'and' => 'and',
    'or' => 'or',
    'in' => 'in', // e.g. every week in January, May and August
    'on' => 'on', // e.g. every day on Tuesday, Wednesday and Friday
    'the' => 'the',
    'on the' => 'on the', // e.g. every year on the 1st and 200th day
    'every %count% years' => 'every %count% years',
    'every year' => 'every year',
    'every_month_list' => 'every', // e.g. every January, May and August
    'every %count% months' => 'every %count% months',
    'every month' => 'every month',
    'every %count% weeks' => 'every %count% weeks',
    'every week' => 'every week',
    'every %count% days' => 'every %count% days',
    'every day' => 'every day',
    'last' => 'last', // e.g. 2nd last Friday
    'days' => 'days',
    'day' => 'day',
    'weeks' => 'weeks',
    'week' => 'week',
    'ordinal_number' => function ($str, $params) { // formats a number with a prefix e.g. every year on the 1st and 200th day
        $number = $params['number'];

        $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');

        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            $abbreviation = $number.'th';
        } else {
            $abbreviation = $number.$ends[$number % 10];
        }

        return $abbreviation;
    },
);

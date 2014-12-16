<?php

return array(
    'Unable to fully convert this rrule to text.' => 'RRule kann nicht vollständig zu Text konvertiert werden.',
    'for %count% times' => '%count% Mal',
    'for %count% time' => '%count% Mal',
    '(~ approximate)' => '(~ ungefähr)',
    'until %date%' => 'bis %date%', // e.g. every year until July 4, 2014
    'day_date' => defined('PHP_WINDOWS_VERSION_BUILD') ? '%#d. %B, %Y' : '%e. %B, %Y',
    'and' => 'und',
    'or' => 'oder',
    'in' => 'im', // e.g. every week in January, May and August
    'on' => 'am', // e.g. every day on Tuesday, Wednesday and Friday
    'the' => 'das',
    'on the' => 'am', // e.g. every year on the 1st and 200th day
    'every %count% years' => 'alle %count% Jahre',
    'every year' => 'jedes Jahr',
    'every_month_list' => 'jeden', // e.g. every January, May and August
    'every %count% months' => 'alle %count% Monate',
    'every month' => 'jeden Monat',
    'every %count% weeks' => 'alle %count% Wochen',
    'every week' => 'jede Woche',
    'every %count% days' => 'alle %count% Tage',
    'every day' => 'jeden Tag',
    'last' => 'letzte', // e.g. 2nd last Friday
    'days' => 'Tage',
    'day' => 'Tag',
    'weeks' => 'Wochen',
    'week' => 'Woche',
    'ordinal_number' => function ($str, $params) { // formats a number with a prefix e.g. every year on the 1st and 200th day
        return $params['number'] . '.';
    },
);

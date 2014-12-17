<?php

return array(
    'Unable to fully convert this rrule to text.' => 'Non è possibile convertire questo rrule in testo.',
    'for %count% times' => 'per %count% volte',
    'for %count% time' => 'per una %count% volta',
    '(~ approximate)' => '(~ approssimato)',
    'until %date%' => 'fino al %date%', // e.g. every year until July 4, 2014
    'day_date' => defined('PHP_WINDOWS_VERSION_BUILD') ? '%#d %B, %Y' : '%e %B, %Y',
    'and' => 'e',
    'or' => 'o',
    'in' => 'in', // e.g. every week in January, May and August
    'on' => 'il', // e.g. every day on Tuesday, Wednesday and Friday
    'the' => 'il',
    'on the' => 'il', // e.g. every year on the 1st and 200th day
    'every %count% years' => 'ogni %count% anni',
    'every year' => 'ogni anno',
    'every_month_list' => 'ogni', // e.g. every January, May and August
    'every %count% months' => 'ogni %count% mesi',
    'every month' => 'ogni mese',
    'every %count% weeks' => 'ogni %count% settimane',
    'every week' => 'ogni settimana',
    'every %count% days' => 'ogni %count% giorni',
    'every day' => 'ogni giorno',
    'last' => 'scorso', // e.g. 2nd last Friday
    'days' => 'giorni',
    'day' => 'giorno',
    'weeks' => 'settimane',
    'week' => 'settimana',
    'ordinal_number' => function ($str, $params) { // formats a number with a prefix e.g. every year on the 1st and 200th day
        $number = $params['number'];

        if ($number == 1) {
            return $number;
        }

        return $number;
    },
);

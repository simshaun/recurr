<?php

return array(
    'Unable to fully convert this rrule to text.' => 'Cette règle de récurrence n\'a pas pu être convertie en texte.',
    'for %count% times' => '%count% fois',
    'for %count% time' => '%count% fois',
    '(~ approximate)' => '(~ approximation)',
    'until %date%' => 'jusqu\'au %date%', // e.g. every year until July 4, 2014
    'day_date' => defined('PHP_WINDOWS_VERSION_BUILD') ? '%#d %B, %Y' : '%e %B, %Y',
    'and' => 'et',
    'or' => 'ou',
    'in' => 'en', // e.g. every week in January, May and August
    'on' => 'le', // e.g. every day on Tuesday, Wednesday and Friday
    'the' => 'le',
    'on the' => 'le', // e.g. every year on the 1st and 200th day
    'every %count% years' => 'tous les %count% ans',
    'every year' => 'chaque année',
    'every_month_list' => 'chaque', // e.g. every January, May and August
    'every %count% months' => 'tous les %count% mois',
    'every month' => 'chaque mois',
    'every %count% weeks' => 'toutes les %count% semaines',
    'every week' => 'chaque semaine',
    'every %count% days' => 'tous les %count% jours',
    'every day' => 'chaque jour',
    'last' => 'dernier', // e.g. 2nd last Friday
    'days' => 'jours',
    'day' => 'jour',
    'weeks' => 'semaines',
    'week' => 'semaine',
    'ordinal_number' => function ($str, $params) { // formats a number with a prefix e.g. every year on the 1st and 200th day
        $number = $params['number'];

        if ($number == 1) {
            return $number.'er';
        }

        return $number.'ème';
    },
);

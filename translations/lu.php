<?php

// sunday first as date('w') is zero-based on sunday
$days = array(
    'sonndeg',
    'méindeg',
    'dënschdeg',
    'mëttwoch',
    'donneschdeg',
    'freideg',
    'samschdeg',
);
$months = array(
    'Januar',
    'Februar',
    'Mäerz',
    'Abrëll',
    'Mee',
    'Juni',
    'Juli',
    'August',
    'September',
    'Oktober',
    'November',
    'Dezember',
);

return array(
    'Unable to fully convert this rrule to text.' => 'Dës Widderhuelungsregel konnt net an Text ëmgewandelt ginn.',
    'for %count% times' => '%count% Kéieren',
    'for one time' => 'eemol',
    '(~ approximate)' => '(~ approximatioun)',
    'until %date%' => 'bis %date%', // e.g. every year until July 4, 2014
    'day_date' => function ($str, $params) use ($days, $months) { // outputs a day date, e.g. 4. Juli, 2014
        return date('j. ', $params['date']) . $months[date('n', $params['date']) - 1] . date(', Y', $params['date']);
    },
    'day_month' => function ($str, $params) use ($days, $months) { // outputs a day month, e.g. July 4
        return $params['day'].'. '.$months[$params['month'] - 1];
    },
    'day_names' => $days,
    'month_names' => $months,
    'and' => 'an',
    'or' => 'oder',
    'in_month' => 'am', // e.g. weekly in January, May and August
    'in_week' => 'an', // e.g. yearly in week 3
    'on' => 'op', // e.g. every day on Tuesday, Wednesday and Friday
    'the_for_monthday' => 'um', // e.g. monthly on Tuesday the 1st
    'the_for_weekday' => '', // e.g. monthly on the 4th Monday
    'on the' => 'um', // e.g. every year on the 1st and 200th day
    'of_the_month' => 'vum Mount', // e.g. every year on the 2nd or 3rd of the month
    'every %count% years' => 'all %count% Joer',
    'every year' => 'all Joer',
    'every_month_list' => 'all', // e.g. every January, May and August
    'every %count% months' => 'all %count% Mount',
    'every month' => 'all Mount',
    'every %count% weeks' => 'all %count% Woch',
    'every week' => 'all Woch',
    'every %count% days' => 'all %count% Dag',
    'every day' => 'all Dag',
    'every %count% hours' => 'all %count% Stonnen',
    'every hour' => 'all Stonn',
    'last' => 'leschte', // e.g. 2nd last Friday
    'days' => 'Deeg',
    'day' => 'Dag',
    'weeks' => 'Wochen',
    'week' => 'Woch',
    'hours' => 'Stonnen',
    'hour' => 'Stonn',
    // formats a number with a prefix e.g. every year on the 1st and 200th day
    // negative numbers should be handled as in '5th to the last' or 'last'
    //
    // if has_negatives is true in the params, it is good form to add 'day' after
    // each number, as in: 'every month on the 5th day or 2nd to the last day' or
    // it may be confusing like 'every month on the 5th or 2nd to the last day'
    'ordinal_number' => function ($str, $params) {
        $number = $params['number'];

        $suffix = '';
        $isNegative = $number < 0;

        if ($number == -1) {
            $abbreviation = 'lescht';
        } elseif ($number == -2) {
            $abbreviation = 'vorletzten';
        } elseif ($number == -3) {
            $abbreviation = 'drittletzten';
        } elseif ($number == -4) {
            $abbreviation = 'viertletzten';
        } elseif ($number == -5) {
            $abbreviation = 'fünftletzten';
        } elseif ($number == -6) {
            $abbreviation = 'sechstletzten';
        } elseif ($number == -7) {
            $abbreviation = 'siebtletzten';
        } elseif ($number == -8) {
            $abbreviation = 'achtletzten';
        } elseif ($number == -9) {
            $abbreviation = 'neuntletzten';
        } elseif ($number == -10) {
            $abbreviation = 'zehntletzten';
        } elseif ($number == -11) {
            $abbreviation = 'elftletzten';
        } elseif ($isNegative) {
            $number = abs($number);
            $abbreviation = $number . 't lescht';
        } else {
            $abbreviation = $number . '.';
        }

        if (!empty($params['has_negatives']) && $isNegative) {
            $suffix .= ' Dag';
        }

        return $abbreviation . $suffix;
    },
);

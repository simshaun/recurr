<?php

// sunday first as date('w') is zero-based on sunday
$days = array(
    'igandea',
    'astelehena',
    'asteartea',
    'asteazkena',
    'osteguna',
    'ostirala',
    'larunbata',
);
$months = array(
    'urtarrila',
    'otsaila',
    'martxoa',
    'apirila',
    'maiatza',
    'ekaina',
    'uztaila',
    'abuztua',
    'iraila',
    'urria',
    'azaroa',
    'abendua',
);

return array(
    'Unable to fully convert this rrule to text.' => 'Ezin izan da rrule testura osoki bihurtu.',
    'for %count% times' => '%count% aldiz',
    'for one time' => 'behin',
    '(~ approximate)' => '(~ inguru)',
    'until %date%' => '%date% arte', // e.g. every year until July 4, 2014
    'day_date' => function ($str, $params) use ($days, $months) { // outputs a day date, e.g. July 4, 2014
        return date('Y', $params['date']) . '(e)ko ' . $months[date('n', $params['date']) - 1] . 'ren ' . date('j', $params['date']) . 'a';
    },
    'day_month' => function ($str, $params) use ($days, $months) { // outputs a day month, e.g. July 4
        return $months[$params['month'] - 1] . 'ak '. $params['day'];
    },
    'day_names' => $days,
    'month_names' => $months,
    'and' => 'eta',
    'or' => 'edo',
    'in_month' => 'hilabete hauetan:', // e.g. weekly in January, May and August
    'in_week' => 'aste hauetan:', // e.g. yearly in week 3
    'on' => 'egun hauetan:', // e.g. every day on Tuesday, Wednesday and Friday
    'the_for_monthday' => '', // e.g. monthly on Tuesday the 1st
    'the_for_weekday' => '', // e.g. monthly on the 4th Monday
    'on the' => 'egun hauetan:', // e.g. every year on the 1st and 200th day
    'of_the_month' => '', // e.g. every year on the 2nd or 3rd of the month
    'every %count% years' => '%count% urtero',
    'every year' => 'urtero',
    'every_month_list' => 'hilabete hauetan:', // e.g. every January, May and August
    'every %count% months' => '%count% hilabetero',
    'every month' => 'hilabetero',
    'every %count% weeks' => '%count% astero',
    'every week' => 'astero',
    'every %count% days' => '%count% egunero',
    'every day' => 'egunero',
    'every %count% hours' => '%count% orduro',
    'every hour' => 'orduro',
    'last' => 'azken', // e.g. 2nd last Friday
    'days' => 'egun',
    'day' => 'egun',
    'weeks' => 'aste',
    'week' => 'aste',
    'hours' => 'ordu',
    'hour' => 'ordu',
    // formats a number with a prefix e.g. every year on the 1st and 200th day
    // negative numbers should be handled as in '5th to the last' or 'last'
    //
    // if has_negatives is true in the params, it is good form to add 'day' after
    // each number, as in: 'every month on the 5th day or 2nd to the last day' or
    // it may be confusing like 'every month on the 5th or 2nd to the last day'
    'ordinal_number' => function ($str, $params) {
        
        $number = $params['number'];

        $ends = array('.', '.', '.', '.', '.', '.', '.', '.', '.', '.');
        $prefix = '';
        $suffix = '';

        $isNegative = $number < 0;

        if ($number == -1) {
            $abbreviation = 'azken';
        } else if ($number == -2) {
               $abbreviation = 'azken aurreko';
        } else {
            if ($isNegative) {
                $number = abs($number);
                $prefix = 'azkenetik hasita ';
            }
            if ($params['day_in_month']) {
                $abbreviation = $number . '. eguna';
            } else {
                $abbreviation = $number . '.';
            }
        }

        return $prefix . $abbreviation . $suffix;
    },
);

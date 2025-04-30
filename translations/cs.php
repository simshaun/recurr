<?php

// neděle jako první, protože date('w') je 0 pro neděli
$days = array(
    'neděle',
    'pondělí',
    'úterý',
    'středa',
    'čtvrtek',
    'pátek',
    'sobota',
);
$daysGenitive = [
	'neděle' => 'neděli', 'pondělí' => 'pondělí', 'úterý' => 'úterý',
	'středa' => 'středu', 'čtvrtek' => 'čtvrtek', 'pátek' => 'pátek', 'sobota' => 'sobotu',
];

$months = array(
    'leden',
    'únor',
    'březen',
    'duben',
    'květen',
    'červen',
    'červenec',
    'srpen',
    'září',
    'říjen',
    'listopad',
    'prosinec',
);
$monthsGenitive = [
	'leden' => 'ledna', 'únor' => 'února', 'březen' => 'března',
	'duben' => 'dubna', 'květen' => 'května', 'červen' => 'června',
	'červenec' => 'července', 'srpen' => 'srpna', 'září' => 'září',
	'říjen' => 'října', 'listopad' => 'listopadu', 'prosinec' => 'prosince',
];

return array(
    'Unable to fully convert this rrule to text.' => 'Nelze plně převést toto pravidlo na text.',
    'for %count% times' => 'celkem %count% krát',
    'for one time' => 'jednou',
    '(~ approximate)' => '(~ přibližně)',
    'until %date%' => 'do %date%',
    'day_date' => function ($str, $params) use ($months, $days) {
		$timestamp = $params['date'];
		$day = intval(date('j', $timestamp));
		$month = intval(date('n', $timestamp));
		$year = date('Y', $timestamp);
		$weekday = date('w', $timestamp); // 0 = neděle, 6 = sobota

		$dayName = $days[$weekday];
		$dayNameGen = $daysGenitive[$dayName] ?? $dayName;
		$monthGen = $monthsGenitive[$months[$month - 1]] ?? $months[$month - 1];

		return "{$dayNameGen} {$day}. {$monthGen} {$year}";
	},


	'day_month' => function ($str, $params) use ($months) {

		$month = intval($params['month']);
		$day = intval($params['day']);
		// Skloňovaný měsíc
		$monthGen = $monthsGenitive[$months[$month - 1]] ?? $months[$month - 1];

		return "{$day}. {$monthGen}";
	},
    'day_names' => $days,
    'month_names' => $months,
    'and' => 'a',
    'or' => 'nebo',
    'in_month' => 'v',
    'in_week' => 'v týdnu',
    'on' => 'v',
    'the_for_monthday' => '',
    'the_for_weekday' => '',
    'on the' => 'dne',
    'of_the_month' => 'v měsíci',
    'every %count% years' => function ($str, $params) use ($days, $months) {
		if ($params['count'] == 1) {
			return 'každý rok';
		} else if ($params['count'] <= 4) {
			return 'každé ' . $params['count'] . ' roky';
		} else {
			return 'každých ' . $params['count'] . ' let';
		}
	},
    'every year' => 'každý rok',
    'every_month_list' => 'každý',
    'every %count% months' => function ($str, $params) {
		if ($params['count'] == 1) {
			return 'každý měsíc';
		} else if ($params['count'] <= 4) {
			return 'každé ' . $params['count'] . ' měsíce';
		} else {
			return 'každých ' . $params['count'] . ' měsíců';
		}
	},
    'every month' => 'každý měsíc',
    'every %count% weeks' => function ($str, $params) {
		if ($params['count'] == 1) {
			return 'každý týden';
		} else if ($params['count'] <= 4) {
			return 'každé ' . $params['count'] . ' týdny';
		} else {
			return 'každý ' . $params['count'] . ' týden';
		}
	},
    'every week' => 'každý týden',
    'every %count% days' => function ($str, $params) {
		if ($params['count'] == 1) {
			return 'každý den';
		} else if ($params['count'] <= 4) {
			return 'každé ' . $params['count'] . ' dny';
		} else {
			return 'každý ' . $params['count'] . ' den';
		}
	},
    'every day' => 'každý den',
    'every %count% hours' => function ($str, $params) {
		if ($params['count'] == 1) {
			return 'každou hodinu';
		} else if ($params['count'] <= 4) {
			return 'každé ' . $params['count'] . ' hodiny';
		} else {
			return 'každých ' . $params['count'] . ' hodin';
		}
	},
    'every hour' => 'každou hodinu',
    'last' => 'poslední',
    'days' => 'dní',
    'day' => 'den',
    'weeks' => 'týdnů',
    'week' => 'týden',
    'hours' => 'hodin',
    'hour' => 'hodina',
	// formats a number with a prefix e.g. every year on the 1st and 200th day
    // negative numbers should be handled as in '5th to the last' or 'last'
    //
    // if has_negatives is true in the params, it is good form to add 'day' after
    // each number, as in: 'every month on the 5th day or 2nd to the last day' or
    // it may be confusing like 'every month on the 5th or 2nd to the last day'
    'ordinal_number' => function ($str, $params) {
        $number = $params['number'];

        $ends = array('.', '.', '.', '.', '.', '.', '.', '.', '.', '.');
        $suffix = '';

        $isNegative = $number < 0;

        if ($number == -1) {
            $abbreviation = 'poslední';
        } else {
            if ($isNegative) {
                $number = abs($number);
                $suffix = '. od konce';
            }

            $abbreviation = $number . '.';
        }

        if (!empty($params['has_negatives'])) {
            $suffix .= ' den';
        }

        return $abbreviation . $suffix;
    },
);

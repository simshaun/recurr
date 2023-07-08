<?php

// sunday first as date('w') is zero-based on sunday
$days = array(
    '星期日',
    '星期一',
    '星期二',
    '星期三',
    '星期四',
    '星期五',
    '星期六',
);
$months = array(
    '一月',
    '二月',
    '三月',
    '四月',
    '五月',
    '六月',
    '七月',
    '八月',
    '九月',
    '十月',
    '十一月',
    '十二月',
);

return array(
    'Unable to fully convert this rrule to text.' => 'Unable to fully convert this rrule to text.',
    'for %count% times' => '共 %count% 次',
    'for one time' => '一次',
    '(~ approximate)' => '(~ 大约)',
    'until %date%' => '直到 %date%', // e.g. every year until July 4, 2014
    'day_date' => function ($str, $params) use ($days, $months) { // outputs a day date, e.g. July 4, 2014
        return date('Y-m-d', $params['date']);
    },
    'day_month' => function ($str, $params) use ($days, $months) { // outputs a day month, e.g. July 4
        return $months[$params['month'] - 1] . ' '. $params['day'];
    },
    'day_names' => $days,
    'month_names' => $months,
    'and' => '和',
    'or' => '或',
    'in_month' => '在', // e.g. weekly in January, May and August
    'in_week' => '在周', // e.g. yearly in week 3
    'on' => '在', // e.g. every day on Tuesday, Wednesday and Friday
    'the_for_monthday' => '', // e.g. monthly on Tuesday the 1st
    'the_for_weekday' => '', // e.g. monthly on the 4th Monday
    'on the' => '第', // e.g. every year on the 1st and 200th day
    'of_the_month' => '每月', // e.g. every year on the 2nd or 3rd of the month
    'every %count% years' => '每 %count% 年',
    'every year' => '每年',
    'every_month_list' => '每', // e.g. every January, May and August
    'every %count% months' => '每 %count% 月',
    'every month' => 'monthly',
    'every %count% weeks' => '每 %count% 周',
    'every week' => 'weekly',
    'every %count% days' => '每 %count% 天',
    'every day' => '每天',
    'every %count% hours' => '每 %count% 小时',
    'every hour' => '每小时',
    'last' => '最后', // e.g. 2nd last Friday
    'days' => '天',
    'day' => '天',
    'weeks' => '周',
    'week' => '周',
    'hours' => '小时',
    'hour' => '小时',
    // formats a number with a prefix e.g. every year on the 1st and 200th day
    // negative numbers should be handled as in '5th to the last' or 'last'
    //
    // if has_negatives is true in the params, it is good form to add 'day' after
    // each number, as in: 'every month on the 5th day or 2nd to the last day' or
    // it may be confusing like 'every month on the 5th or 2nd to the last day'
    'ordinal_number' => function ($str, $params) {
        $number = $params['number'];

        $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
        $suffix = '';

        $isNegative = $number < 0;

        if ($number == -1) {
            $abbreviation = '最后';
        } else {
            if ($isNegative) {
                $number = abs($number);
                $suffix = ' 倒数';
            }

            if (($number % 100) >= 11 && ($number % 100) <= 13) {
                $abbreviation = $number.'th';
            } else {
                $abbreviation = $number.$ends[$number % 10];
            }
        }

        if (!empty($params['has_negatives'])) {
            $suffix .= ' 天';
        }

        return $abbreviation . $suffix;
    },
);

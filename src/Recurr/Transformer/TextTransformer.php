<?php

namespace Recurr\Transformer;

use Recurr\Rule;

class TextTransformer
{
    protected $fragments = array();

    public function transform(Rule $rule)
    {
        $this->addFragment('every');

        switch ($rule->getFreq()) {
            case 0:
                $this->addYearly($rule);
                break;
            case 1:
                $this->addMonthly($rule);
                break;
            case 2:
                $this->addWeekly($rule);
                break;
            case 3:
                $this->addDaily($rule);
                break;
            case 4:
            case 5:
            case 6:
                return 'Unable to fully convert this rrule to text.';
        }

        $until = $rule->getUntil();
        $count = $rule->getCount();
        if ($until instanceof \DateTime) {
            $this->addFragment('until');
            $this->addFragment($until->format('F j, Y'));
        } else if (!empty($count)) {
            $this->addFragment('for');
            $this->addFragment($count);
            $this->addFragment($this->isPlural($count) ? 'times' : 'time');
        }

        if (!$this->isFullyConvertible($rule)) {
            $this->addFragment('(~ approximate)');
        }

        return implode(' ', $this->fragments);
    }

    protected function isFullyConvertible(Rule $rule)
    {
        if ($rule->getFreq() >= 4) {
            return false;
        }

        $until = $rule->getUntil();
        $count = $rule->getCount();
        if (!empty($until) && !empty($count)) {
            return false;
        }

        $bySecond = $rule->getBySecond();
        $byMinute = $rule->getByMinute();
        $byHour   = $rule->getByHour();

        if (!empty($bySecond) || !empty($byMinute) || !empty($byHour)) {
            return false;
        }

        $byWeekNum = $rule->getByWeekNumber();
        $byYearDay = $rule->getByYearDay();
        if ($rule->getFreq() != 0 && (!empty($byWeekNum) || !empty($byYearDay))) {
            return false;
        }

        return true;
    }

    protected function addYearly(Rule $rule)
    {
        $interval = $rule->getInterval();

        $byMonth = $rule->getByMonth();
        if (!empty($byMonth)) {
            if ($interval != 1) {
                $this->addFragment($interval);
                $this->addFragment('years');
                $this->addFragment('in');
            }

            $this->addByMonth($rule);
        } else {
            if ($interval != 1) {
                $this->addFragment($interval);
            }

            $this->addFragment($this->isPlural($interval) ? 'years' : 'year');
        }

        $byMonthDay = $rule->getByMonthDay();
        $byDay      = $rule->getByDay();
        if (!empty($byMonthDay)) {
            $this->addByMonthDay($rule);
        } else if (!empty($byDay)) {
            $this->addByDay($rule);
        }

        $byYearDay = $rule->getByYearDay();
        if (!empty($byYearDay)) {
            $this->addFragment('on the');
            $this->addFragment($this->getByYearDayAsText($byYearDay));
            $this->addFragment('day');
        }

        $byWeekNum = $rule->getByWeekNumber();
        if (!empty($byWeekNum)) {
            $this->addFragment('in');
            $this->addFragment($this->isPlural(count($byWeekNum)) ? 'weeks' : 'week');
            $this->addFragment($this->getByWeekNumberAsText($byWeekNum));
        }
    }

    protected function addMonthly(Rule $rule)
    {
        $interval = $rule->getInterval();

        $byMonth = $rule->getByMonth();
        if (!empty($byMonth)) {
            if ($interval != 1) {
                $this->addFragment($interval);
                $this->addFragment('months');
                if ($this->isPlural($interval)) {
                    $this->addFragment('in');
                }
            }

            $this->addByMonth($rule);
        } else {
            if ($interval != 1) {
                $this->addFragment($interval);
            }

            $this->addFragment($this->isPlural($interval) ? 'months' : 'month');
        }

        $byMonthDay = $rule->getByMonthDay();
        $byDay      = $rule->getByDay();
        if (!empty($byMonthDay)) {
            $this->addByMonthDay($rule);
        } else if (!empty($byDay)) {
            $this->addByDay($rule);
        }
    }

    protected function addWeekly(Rule $rule)
    {
        $interval = $rule->getInterval();

        if ($interval != 1) {
            $this->addFragment($interval);
            $this->addFragment($this->isPlural($interval) ? 'weeks' : 'week');
        }

        if ($interval == 1) {
            $this->addFragment('week');
        }

        $byMonth = $rule->getByMonth();
        if (!empty($byMonth)) {
            $this->addFragment('in');
            $this->addByMonth($rule);
        }

        $byMonthDay = $rule->getByMonthDay();
        $byDay      = $rule->getByDay();
        if (!empty($byMonthDay)) {
            $this->addByMonthDay($rule);
        } else if (!empty($byDay)) {
            $this->addByDay($rule);
        }
    }

    protected function addDaily(Rule $rule)
    {
        $interval = $rule->getInterval();

        if ($interval != 1) {
            $this->addFragment($interval);
        }

        $this->addFragment($this->isPlural($interval) ? 'days' : 'day');

        $byMonth = $rule->getByMonth();
        if (!empty($byMonth)) {
            $this->addFragment('in');
            $this->addByMonth($rule);
        }

        $byMonthDay = $rule->getByMonthDay();
        $byDay      = $rule->getByDay();
        if (!empty($byMonthDay)) {
            $this->addByMonthDay($rule);
        } else if (!empty($byDay)) {
            $this->addByDay($rule);
        }
    }

    protected function addByMonth(Rule $rule)
    {
        $byMonth = $rule->getByMonth();

        if (empty($byMonth)) {
            return;
        }

        $this->addFragment($this->getByMonthAsText($byMonth));
    }

    protected function addByMonthDay(Rule $rule)
    {
        $byMonthDay = $rule->getByMonthDay();
        $byDay      = $rule->getByDay();

        if (!empty($byDay)) {
            $this->addFragment('on');
            $this->addFragment($this->getByDayAsText($byDay, 'or'));
            $this->addFragment('the');
            $this->addFragment($this->getByMonthDayAsText($byMonthDay, 'or'));
        } else {
            $this->addFragment('on the');
            $this->addFragment($this->getByMonthDayAsText($byMonthDay, 'and'));
        }
    }

    protected function addByDay(Rule $rule)
    {
        $byDay = $rule->getByDay();

        $this->addFragment('on');
        $this->addFragment($this->getByDayAsText($byDay));
    }

    public function getByMonthAsText($byMonth)
    {
        if (empty($byMonth)) {
            return '';
        }

        if (count($byMonth) > 1) {
            sort($byMonth);
        }

        $byMonth = array_map(
            function ($monthInt) {
                return date('F', mktime(1, 1, 1, $monthInt, 1));
            },
            $byMonth
        );

        return $this->getListStringFromArray($byMonth);
    }

    public function getByDayAsText($byDay, $listSeparator = 'and')
    {
        if (empty($byDay)) {
            return '';
        }

        $map = array(
            'SU' => null,
            'MO' => null,
            'TU' => null,
            'WE' => null,
            'TH' => null,
            'FR' => null,
            'SA' => null
        );

        $timestamp = mktime(1, 1, 1, 1, 12, 2014); // A Sunday
        foreach (array_keys($map) as $short) {
            $long        = date('l', $timestamp);
            $map[$short] = $long;
            $timestamp += 86400;
        }

        $byDay = array_map(
            function ($short) use ($map) {
                $short = strtoupper($short);

                if (!isset($map[$short])) {
                    throw new \RuntimeException("byDay $short could not be transformed");
                }

                return $map[$short];
            },
            $byDay
        );

        return $this->getListStringFromArray($byDay, $listSeparator);
    }

    public function getByMonthDayAsText($byMonthDay, $listSeparator = 'and')
    {
        if (empty($byMonthDay)) {
            return '';
        }

        sort($byMonthDay);

        $byMonthDay = array_map(array($this, 'getOrdinalNumber'), $byMonthDay);

        return $this->getListStringFromArray($byMonthDay, $listSeparator);
    }

    public function getByYearDayAsText($byYearDay)
    {
        if (empty($byYearDay)) {
            return '';
        }

        sort($byYearDay);

        $byYearDay = array_map(array($this, 'getOrdinalNumber'), $byYearDay);

        return $this->getListStringFromArray($byYearDay);
    }

    public function getByWeekNumberAsText($byWeekNum)
    {
        if (empty($byWeekNum)) {
            return '';
        }

        if (count($byWeekNum) > 1) {
            sort($byWeekNum);
        }

        return $this->getListStringFromArray($byWeekNum);
    }

    protected function addFragment($fragment)
    {
        $this->fragments[] = $fragment;
    }

    public function resetFragments()
    {
        $this->fragments = array();
    }

    protected function isPlural($number)
    {
        return $number % 100 != 1;
    }

    protected function getOrdinalNumber($number)
    {
        if (!ctype_digit($number)) {
            throw new \RuntimeException('$number must be a whole number');
        }

        $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');

        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            $abbreviation = $number.'th';
        } else {
            $abbreviation = $number.$ends[$number % 10];
        }

        return $abbreviation;
    }

    protected function getListStringFromArray($values, $separator = 'and')
    {
        if (!is_array($values)) {
            throw new \RuntimeException('$values must be an array.');
        }

        $numValues = count($values);

        if (!$numValues) {
            return '';
        }

        if ($numValues == 1) {
            reset($values);

            return current($values);
        }

        if ($numValues == 2) {
            return implode(" $separator ", $values);
        }

        $lastValue = array_pop($values);
        $output    = implode(', ', $values);
        $output .= " $separator ".$lastValue;

        return $output;
    }
}

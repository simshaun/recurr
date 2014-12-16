<?php

namespace Recurr\Transformer;

use Recurr\Rule;

class TextTransformer
{
    protected $fragments = array();
    protected $translator;

    public function __construct(TranslatorInterface $translator = null)
    {
        $this->translator = $translator ?: new Translator('en');
    }

    public function transform(Rule $rule)
    {
        $this->fragments = array();

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
                return $this->translator->trans('Unable to fully convert this rrule to text.');
        }

        $until = $rule->getUntil();
        $count = $rule->getCount();
        if ($until instanceof \DateTime) {
            $dateFormatted = str_replace('  ', ' ', strftime($this->translator->trans('day_date'), $until->format('U')));
            $this->addFragment($this->translator->trans('until %date%', array('date' => $dateFormatted)));
        } else if (!empty($count)) {
            if ($this->isPlural($count)) {
                $this->addFragment($this->translator->trans('for %count% times', array('count' => $count)));
            } else {
                $this->addFragment($this->translator->trans('for %count% time', array('count' => $count)));
            }
        }

        if (!$this->isFullyConvertible($rule)) {
            $this->addFragment($this->translator->trans('(~ approximate)'));
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

        if (!empty($byMonth) && $interval == 1) {
            $this->addFragment($this->translator->trans('every_month_list'));
        } else {
            $this->addFragment($this->translator->trans($this->isPlural($interval) ? 'every %count% years' : 'every year', array('count' => $interval)));
        }

        if (!empty($byMonth)) {
            if ($interval != 1) {
                $this->addFragment($this->translator->trans('in'));
            }

            $this->addByMonth($rule);
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
            $this->addFragment($this->translator->trans('on the'));
            $this->addFragment($this->getByYearDayAsText($byYearDay));
            $this->addFragment($this->translator->trans('day'));
        }

        $byWeekNum = $rule->getByWeekNumber();
        if (!empty($byWeekNum)) {
            $this->addFragment($this->translator->trans('in'));
            $this->addFragment($this->translator->trans($this->isPlural(count($byWeekNum)) ? 'weeks' : 'week'));
            $this->addFragment($this->getByWeekNumberAsText($byWeekNum));
        }
    }

    protected function addMonthly(Rule $rule)
    {
        $interval = $rule->getInterval();
        $byMonth = $rule->getByMonth();

        if (!empty($byMonth) && $interval == 1) {
            $this->addFragment($this->translator->trans('every_month_list'));
        } else {
            $this->addFragment($this->translator->trans($this->isPlural($interval) ? 'every %count% months' : 'every month', array('count' => $interval)));
        }

        if (!empty($byMonth)) {
            if ($interval != 1) {
                $this->addFragment($this->translator->trans('in'));
            }

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

    protected function addWeekly(Rule $rule)
    {
        $interval = $rule->getInterval();
        $byMonth = $rule->getByMonth();

        $this->addFragment($this->translator->trans($this->isPlural($interval) ? 'every %count% weeks' : 'every week', array('count' => $interval)));

        if (!empty($byMonth)) {
            $this->addFragment($this->translator->trans('in'));
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
        $byMonth = $rule->getByMonth();

        $this->addFragment($this->translator->trans($this->isPlural($interval) ? 'every %count% days' : 'every day', array('count' => $interval)));

        if (!empty($byMonth)) {
            $this->addFragment($this->translator->trans('in'));
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
            $this->addFragment($this->translator->trans('on'));
            $this->addFragment($this->getByDayAsText($byDay, $this->translator->trans('or')));
            $this->addFragment($this->translator->trans('the'));
            $this->addFragment($this->getByMonthDayAsText($byMonthDay, $this->translator->trans('or')));
        } else {
            $this->addFragment($this->translator->trans('on the'));
            $this->addFragment($this->getByMonthDayAsText($byMonthDay, $this->translator->trans('and')));
        }
    }

    protected function addByDay(Rule $rule)
    {
        $byDay = $rule->getByDay();

        $this->addFragment($this->translator->trans('on'));
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
                return strftime('%B', mktime(1, 1, 1, $monthInt, 1));
            },
            $byMonth
        );

        return $this->getListStringFromArray($byMonth);
    }

    public function getByDayAsText($byDay, $listSeparator = null)
    {
        if (empty($byDay)) {
            return '';
        }

        if (null === $listSeparator) {
            $listSeparator = $this->translator->trans('and');
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
            $long        = strftime('%A', $timestamp);
            $map[$short] = $long;
            $timestamp += 86400;
        }

        $numOrdinals = 0;
        foreach ($byDay as $key => $short) {
            $day    = strtoupper($short);
            $string = '';

            if (preg_match('/([+-]?)([0-9]*)([A-Z]+)/', $short, $parts)) {
                $symbol = $parts[1];
                $nth    = $parts[2];
                $day    = $parts[3];

                if (!empty($nth)) {
                    ++$numOrdinals;
                    if ($symbol != '-' || $nth != 1) {
                        $string .= $this->getOrdinalNumber($nth);
                    }
                    if ($symbol == '-') {
                        $string .= ' ' . $this->translator->trans('last');
                    }
                }
            }

            if (!isset($map[$day])) {
                throw new \RuntimeException("byDay $short could not be transformed");
            }

            if (!empty($string)) {
                $string .= ' ';
            }

            $byDay[$key] = ltrim($string.$map[$day]);
        }

        $output = $numOrdinals ? $this->translator->trans('the') . ' ' : null;
        $output .= $this->getListStringFromArray($byDay, $listSeparator);

        return $output;
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

        return $this->translator->trans('ordinal_number', array('number' => $number));
    }

    protected function getListStringFromArray($values, $separator = null)
    {
        if (null === $separator) {
            $separator = $this->translator->trans('and');
        }

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

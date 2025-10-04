<?php

namespace Recurr\Transformer;

use Recurr\Rule;

class TextTransformer
{
    /**
     * @var string[]
     */
    protected array $fragments = [];

    protected TranslatorInterface $translator;

    public function __construct(?TranslatorInterface $translator = null)
    {
        $this->translator = $translator ?: new Translator('en');
    }

    public function transform(Rule $rule): string
    {
        $this->fragments = [];

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
                $this->addHourly($rule);
                break;
            case 5:
            case 6:
                return $this->transString('Unable to fully convert this rrule to text.');
        }

        $until = $rule->getUntil();
        $count = $rule->getCount();
        if ($until instanceof \DateTimeInterface) {
            $dateFormatted = $this->transString('day_date', ['date' => $until->format('U')]);
            $this->addFragment($this->transString('until %date%', ['date' => $dateFormatted]));
        } elseif ($count !== null) {
            if ($this->isPlural($count)) {
                $this->addFragment($this->transString('for %count% times', ['count' => $count]));
            } else {
                $this->addFragment($this->transString('for one time'));
            }
        }

        if (!$this->isFullyConvertible($rule)) {
            $this->addFragment($this->transString('(~ approximate)'));
        }

        return implode(' ', $this->fragments);
    }

    protected function isFullyConvertible(Rule $rule): bool
    {
        if ($rule->getFreq() >= 5) {
            return false;
        }

        $until = $rule->getUntil();
        $count = $rule->getCount();
        if ($until !== null && $count !== null) {
            return false;
        }

        $bySecond = $rule->getBySecond();
        $byMinute = $rule->getByMinute();
        $byHour = $rule->getByHour();

        if (!empty($bySecond) || !empty($byMinute) || !empty($byHour)) {
            return false;
        }

        $byWeekNum = $rule->getByWeekNumber();
        $byYearDay = $rule->getByYearDay();
        if ($rule->getFreq() === null || $rule->getFreq() > 0 && (!empty($byWeekNum) || !empty($byYearDay))) {
            return false;
        }

        return true;
    }

    protected function addYearly(Rule $rule): void
    {
        $interval = $rule->getInterval();
        $byMonth = $rule->getByMonth();
        $byMonthDay = $rule->getByMonthDay();
        $byDay = $rule->getByDay();
        $byYearDay = $rule->getByYearDay();
        $byWeekNum = $rule->getByWeekNumber();

        if (!empty($byMonth) && count($byMonth) > 1 && $interval === 1) {
            $this->addFragment($this->transString('every_month_list'));
        } else {
            $this->addFragment($this->transString(
                $this->isPlural($interval) ? 'every %count% years' : 'every year',
                ['count' => $interval]
            ));
        }

        $hasNoOrOneByMonth = $byMonth === null || count($byMonth) <= 1;
        if ($hasNoOrOneByMonth && empty($byMonthDay) && empty($byDay) && empty($byYearDay) && empty($byWeekNum)) {
            $this->addFragment($this->transString('on'));
            $this->addFragment(
                $this->transString('day_month', [
                    'month' => !empty($byMonth) ? $byMonth[0] : $rule->getStartDate()?->format('n'),
                    'day' => $rule->getStartDate()?->format('d'),
                ])
            );
        } elseif (!empty($byMonth)) {
            if ($interval != 1) {
                $this->addFragment($this->transString('in_month'));
            }

            $this->addByMonth($rule);
        }

        if (!empty($byMonthDay)) {
            $this->addByMonthDay($rule);
            $this->addFragment($this->transString('of_the_month'));
        } elseif (!empty($byDay)) {
            $this->addByDay($rule);
        }

        if (!empty($byYearDay)) {
            $this->addFragment($this->transString('on the'));
            $this->addFragment($this->getByYearDayAsText($byYearDay));
            $this->addFragment($this->transString('day'));
        }

        if (!empty($byWeekNum)) {
            $this->addFragment($this->transString('in_week'));
            $this->addFragment($this->transString($this->isPlural(count($byWeekNum)) ? 'weeks' : 'week'));
            $this->addFragment($this->getByWeekNumberAsText($byWeekNum));
        }

        if (empty($byMonthDay) && empty($byYearDay) && empty($byDay) && !empty($byWeekNum)) {
            $this->addDayOfWeek($rule);
        }
    }

    protected function addMonthly(Rule $rule): void
    {
        $interval = $rule->getInterval();
        $byMonth = $rule->getByMonth();

        if (!empty($byMonth) && $interval === 1) {
            $this->addFragment($this->transString('every_month_list'));
        } else {
            $this->addFragment($this->transString(
                $this->isPlural($interval)
                    ? 'every %count% months'
                    : 'every month', ['count' => $interval]
            ));
        }

        if (!empty($byMonth)) {
            if ($interval != 1) {
                $this->addFragment($this->transString('in_month'));
            }

            $this->addByMonth($rule);
        }

        $byMonthDay = $rule->getByMonthDay();
        $byDay = $rule->getByDay();
        if (!empty($byMonthDay)) {
            $this->addByMonthDay($rule);
        } elseif (!empty($byDay)) {
            $this->addByDay($rule);
        }
    }

    protected function addWeekly(Rule $rule): void
    {
        $interval = $rule->getInterval();
        $byMonth = $rule->getByMonth();
        $byMonthDay = $rule->getByMonthDay();
        $byDay = $rule->getByDay();

        $this->addFragment($this->transString(
            $this->isPlural($interval)
                ? 'every %count% weeks'
                : 'every week', ['count' => $interval]
        ));

        if (empty($byMonthDay) && empty($byDay)) {
            $this->addDayOfWeek($rule);
        }

        if (!empty($byMonth)) {
            $this->addFragment($this->transString('in_month'));
            $this->addByMonth($rule);
        }

        if (!empty($byMonthDay)) {
            $this->addByMonthDay($rule);
            $this->addFragment($this->transString('of_the_month'));
        } elseif (!empty($byDay)) {
            $this->addByDay($rule);
        }
    }

    protected function addDaily(Rule $rule): void
    {
        $interval = $rule->getInterval();
        $byMonth = $rule->getByMonth();

        $this->addFragment($this->transString(
            $this->isPlural($interval)
                ? 'every %count% days'
                : 'every day', ['count' => $interval]
        ));

        if (!empty($byMonth)) {
            $this->addFragment($this->transString('in_month'));
            $this->addByMonth($rule);
        }

        $byMonthDay = $rule->getByMonthDay();
        $byDay = $rule->getByDay();
        if (!empty($byMonthDay)) {
            $this->addByMonthDay($rule);
            $this->addFragment($this->transString('of_the_month'));
        } elseif (!empty($byDay)) {
            $this->addByDay($rule);
        }
    }

    protected function addHourly(Rule $rule): void
    {
        $interval = $rule->getInterval();
        $byMonth = $rule->getByMonth();

        $this->addFragment($this->transString(
            $this->isPlural($interval)
                ? 'every %count% hours'
                : 'every hour', ['count' => $interval]
        ));

        if (!empty($byMonth)) {
            $this->addFragment($this->transString('in_month'));
            $this->addByMonth($rule);
        }

        $byMonthDay = $rule->getByMonthDay();
        $byDay = $rule->getByDay();
        if (!empty($byMonthDay)) {
            $this->addByMonthDay($rule);
            $this->addFragment($this->transString('of_the_month'));
        } elseif (!empty($byDay)) {
            $this->addByDay($rule);
        }
    }

    protected function addByMonth(Rule $rule): void
    {
        $byMonth = $rule->getByMonth();

        if (empty($byMonth)) {
            return;
        }

        $this->addFragment($this->getByMonthAsText($byMonth));
    }

    protected function addByMonthDay(Rule $rule): void
    {
        $byMonthDay = $rule->getByMonthDay();
        $byDay = $rule->getByDay();

        if (!empty($byDay)) {
            $this->addFragment($this->transString('on'));
            $this->addFragment($this->getByDayAsText($byDay, 'or'));
            $this->addFragment($this->transString('the_for_monthday'));
            $this->addFragment($this->getByMonthDayAsText($byMonthDay ?: [], 'or'));
        } else {
            $this->addFragment($this->transString('on the'));
            $this->addFragment($this->getByMonthDayAsText($byMonthDay ?: [], 'and'));
        }
    }

    protected function addByDay(Rule $rule): void
    {
        $byDay = $rule->getByDay();

        $this->addFragment($this->transString('on'));
        $this->addFragment($this->getByDayAsText($byDay ?: []));
    }

    protected function addDayOfWeek(Rule $rule): void
    {
        if (!$rule->getStartDate()) {
            return;
        }

        $this->addFragment($this->transString('on'));

        /**
         * @var array<int, string> $dayNames
         */
        $dayNames = $this->translator->trans('day_names');

        $this->addFragment($dayNames[(int) $rule->getStartDate()->format('w')]);
    }

    /**
     * @param int[] $byMonth
     */
    public function getByMonthAsText(array $byMonth): string
    {
        if (empty($byMonth)) {
            return '';
        }

        if (count($byMonth) > 1) {
            sort($byMonth);
        }

        /**
         * @var array<int, string> $monthNames
         */
        $monthNames = $this->translator->trans('month_names');

        $byMonth = array_map(
            fn ($monthInt) => $monthNames[$monthInt - 1],
            $byMonth
        );

        return $this->getListStringFromArray($byMonth);
    }

    /**
     * @param string[] $byDay
     */
    public function getByDayAsText(array $byDay, string $listSeparator = 'and'): string
    {
        if (empty($byDay)) {
            return '';
        }

        $map = [
            'SU' => null,
            'MO' => null,
            'TU' => null,
            'WE' => null,
            'TH' => null,
            'FR' => null,
            'SA' => null,
        ];

        /**
         * @var array<int, string> $dayNames
         */
        $dayNames = $this->translator->trans('day_names');

        $timestamp = mktime(1, 1, 1, 1, 12, 2014) ?: -1; // A Sunday

        foreach (array_keys($map) as $short) {
            $long = $dayNames[(int) date('w', $timestamp)];
            $map[$short] = $long;
            $timestamp += 86400;
        }

        $numOrdinals = 0;
        foreach ($byDay as $key => $short) {
            $day = strtoupper($short);
            $string = '';

            if (preg_match('/([+-]?)(\d*)([A-Z]+)/', $short, $parts)) {
                $symbol = $parts[1];
                $nth = $parts[2];
                $day = $parts[3];

                if ($nth !== '' && $nth !== '0') {
                    ++$numOrdinals;
                    $string .= $this->getOrdinalNumber((int) ($symbol === '-' ? -$nth : $nth));
                }
            }

            if (!isset($map[$day])) {
                throw new \RuntimeException("byDay $short could not be transformed");
            }

            if ($string !== '' && $string !== '0') {
                $string .= ' ';
            }

            $byDay[$key] = ltrim($string.$map[$day]);
        }

        $output = $numOrdinals !== 0 ? $this->transString('the_for_weekday').' ' : '';
        if ($output === ' ') {
            $output = '';
        }
        $output .= $this->getListStringFromArray($byDay, $listSeparator);

        return $output;
    }

    /**
     * @param int[] $byMonthDay
     */
    public function getByMonthDayAsText(array $byMonthDay, string $listSeparator = 'and'): string
    {
        if (empty($byMonthDay)) {
            return '';
        }

        // sort negative indices in reverse order so we get e.g. 1st, 2nd, 4th, 3rd last, last day
        usort($byMonthDay, function ($a, $b): int {
            if (($a < 0 && $b < 0) || ($a >= 0 && $b >= 0)) {
                return $a - $b;
            }

            return $b - $a;
        });

        // generate ordinal numbers and insert a "on the" for clarity in the middle if we have both
        // positive and negative ordinals. This is to avoid confusing situations like:
        //
        // monthly on the 1st and 2nd to the last day
        //
        // which gets clarified to:
        //
        // monthly on the 1st day and on the 2nd to the last day
        $hadPositives = false;
        $hadNegatives = false;
        foreach ($byMonthDay as $index => $day) {
            $prefix = '';
            if ($day >= 0) {
                $hadPositives = true;
            }
            if ($day < 0) {
                if ($hadPositives && !$hadNegatives && $listSeparator === 'and') {
                    $prefix = $this->transString('on the').' ';
                }
                $hadNegatives = true;
            }
            $byMonthDay[$index] = $prefix.$this->getOrdinalNumber($day, end($byMonthDay) < 0, true);
        }

        return $this->getListStringFromArray($byMonthDay, $listSeparator);
    }

    /**
     * @param int[] $byYearDay
     */
    public function getByYearDayAsText(array $byYearDay): string
    {
        if (empty($byYearDay)) {
            return '';
        }

        // sort negative indices in reverse order so we get e.g. 1st, 2nd, 4th, 3rd last, last day
        usort($byYearDay, function ($a, $b): int {
            if (($a < 0 && $b < 0) || ($a >= 0 && $b >= 0)) {
                return $a - $b;
            }

            return $b - $a;
        });

        $byYearDay = array_map(
            [$this, 'getOrdinalNumber'],
            $byYearDay,
            array_fill(0, count($byYearDay), end($byYearDay) < 0)
        );

        return $this->getListStringFromArray($byYearDay);
    }

    /**
     * @param int[] $byWeekNum
     */
    public function getByWeekNumberAsText(array $byWeekNum): string
    {
        if (empty($byWeekNum)) {
            return '';
        }

        if (count($byWeekNum) > 1) {
            sort($byWeekNum);
        }

        return $this->getListStringFromArray($byWeekNum);
    }

    protected function addFragment(string $fragment): void
    {
        if ($fragment && $fragment !== ' ') {
            $this->fragments[] = $fragment;
        }
    }

    public function resetFragments(): void
    {
        $this->fragments = [];
    }

    /**
     * Get a string translation (helper to satisfy PHPStan when we know the result is a string)
     *
     * @param array<string, string|int|float|bool|null> $params
     */
    protected function transString(string $key, array $params = []): string
    {
        $result = $this->translator->trans($key, $params);
        assert(is_string($result));

        return $result;
    }

    protected function isPlural(int $number): bool
    {
        return $number % 100 != 1;
    }

    protected function getOrdinalNumber(
        string|int $number,
        bool $hasNegatives = false,
        bool $dayInMonth = false,
    ): string {
        if (!preg_match('{^-?\d+$}D', (string) $number)) {
            throw new \RuntimeException('$number must be a whole number');
        }

        return $this->transString('ordinal_number', ['number' => $number, 'has_negatives' => $hasNegatives, 'day_in_month' => $dayInMonth]);
    }

    /**
     * @param array<string|int|float> $values
     */
    protected function getListStringFromArray(array $values, string $separator = 'and'): string
    {
        $separator = $this->transString($separator);

        $numValues = count($values);

        if ($numValues === 0) {
            return '';
        }

        if ($numValues === 1) {
            return (string) reset($values);
        }

        if ($numValues === 2) {
            return implode(" $separator ", $values);
        }

        $lastValue = array_pop($values);
        $output = implode(', ', $values);
        $output .= " $separator ".$lastValue;

        return $output;
    }
}

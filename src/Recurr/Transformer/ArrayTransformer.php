<?php

/*
 * Copyright 2025 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Based on:
 * rrule.js - Library for working with recurrence rules for calendar dates.
 * Copyright 2010, Jakub Roztocil and Lars Schoning
 * https://github.com/jkbr/rrule/blob/master/LICENCE
 */

namespace Recurr\Transformer;

use Recurr\DateExclusion;
use Recurr\DateInclusion;
use Recurr\DateUtil;
use Recurr\Exception\InvalidWeekday;
use Recurr\Frequency;
use Recurr\Recurrence;
use Recurr\RecurrenceCollection;
use Recurr\Rule;
use Recurr\Time;
use Recurr\Weekday;

/**
 * This class is responsible for transforming a Rule in to an array
 * of \DateTimeInterface objects.
 *
 * If a recurrence rule is infinitely recurring, a virtual limit is imposed.
 *
 * @author Shaun Simmons <gh@simshaun.com>
 */
class ArrayTransformer
{
    protected ArrayTransformerConfig $config;

    /**
     * Some versions of PHP are affected by a bug where
     * \DateTimeInterface::createFromFormat('z Y', ...) does not account for leap years.
     */
    protected bool $leapBug;

    /**
     * Construct a new ArrayTransformer
     */
    public function __construct(?ArrayTransformerConfig $config = null)
    {
        $this->config = $config ?: new ArrayTransformerConfig();

        $this->leapBug = DateUtil::hasLeapYearBug();
    }

    public function setConfig(ArrayTransformerConfig $config): void
    {
        $this->config = $config;
    }

    /**
     * Transform a Rule in to an array of \DateTimeInterface objects
     *
     * @param Rule $rule the Rule object
     * @param ConstraintInterface|null $constraint Recurrences must satisfy the constraint to be included in the returned collection
     * @param bool $countConstraintFailures Should recurrences that fail the constraint's test count towards the rule's COUNT limit
     *
     * @return RecurrenceCollection<Recurrence>
     *
     * @throws InvalidWeekday
     */
    public function transform(
        Rule $rule,
        ?ConstraintInterface $constraint = null,
        bool $countConstraintFailures = true,
    ): RecurrenceCollection {
        $start = $rule->getStartDate();
        $end = $rule->getEndDate();
        $until = $rule->getUntil();

        if (!$start instanceof \DateTimeInterface) {
            $start = new \DateTime(
                'now', $until instanceof \DateTimeInterface ? $until->getTimezone() : null
            );
        }

        if (!$end instanceof \DateTimeInterface) {
            $end = $start;
        }

        $durationInterval = $start->diff($end);

        $startDay = (int) $start->format('j');
        $startMonthLength = (int) $start->format('t');
        $fixLastDayOfMonth = false;

        $dt = clone $start;

        $maxCount = $rule->getCount();
        $vLimit = $this->config->getVirtualLimit();

        $freq = $rule->getFreq();
        $weekStart = $rule->getWeekStartAsNum();
        $bySecond = $rule->getBySecond();
        $byMinute = $rule->getByMinute();
        $byHour = $rule->getByHour();
        $byMonth = $rule->getByMonth();
        $byWeekNum = $rule->getByWeekNumber();
        $byYearDay = $rule->getByYearDay();
        $byMonthDay = $rule->getByMonthDay();
        /** @var int[] $byMonthDayNeg */
        $byMonthDayNeg = [];
        $byWeekDay = $rule->getByDayAsWeekdays();
        /** @var int[] $byWeekDayInt */
        $byWeekDayInt = [];
        /** @var Weekday[] $byWeekDayRel */
        $byWeekDayRel = [];
        $bySetPos = $rule->getBySetPosition();

        $implicitByMonthDay = false;
        if (empty($byWeekNum) && empty($byYearDay) && empty($byMonthDay) && empty($byWeekDay)) {
            switch ($freq) {
                case Frequency::YEARLY:
                    if (empty($byMonth)) {
                        $byMonth = [(int) $start->format('n')];
                    }

                    if ($startDay > 28) {
                        $fixLastDayOfMonth = true;
                    }

                    $implicitByMonthDay = true;
                    $byMonthDay = [$startDay];
                    break;
                case Frequency::MONTHLY:
                    if ($startDay > 28) {
                        $fixLastDayOfMonth = true;
                    }

                    $implicitByMonthDay = true;
                    $byMonthDay = [$startDay];
                    break;
                case Frequency::WEEKLY:
                    $byWeekDay = [
                        new Weekday(weekday: DateUtil::getDayOfWeek($start), num: null),
                    ];
                    break;
            }
        }

        if (!$this->config->isLastDayOfMonthFixEnabled()) {
            $fixLastDayOfMonth = false;
        }

        if (is_array($byMonthDay) && count($byMonthDay)) {
            foreach ($byMonthDay as $idx => $day) {
                if ($day < 0) {
                    unset($byMonthDay[$idx]);
                    $byMonthDayNeg[] = $day;
                }
            }
        }

        if (count($byWeekDay) > 0) {
            foreach ($byWeekDay as $day) {
                if (!empty($day->num)) {
                    $byWeekDayRel[] = $day;
                } else {
                    $byWeekDayInt[] = $day->weekday;
                }
            }
        }

        $year = (int) $dt->format('Y');
        $month = (int) $dt->format('n');
        $hour = (int) $dt->format('G');
        $minute = (int) $dt->format('i');
        $second = (int) $dt->format('s');

        $dates = [];
        $total = 1;
        $count = $maxCount;
        $continue = true;
        $iterations = 0;
        while ($continue) {
            $dtInfo = DateUtil::getDateInfo($dt);

            $tmp = DateUtil::getDaySet($rule, $dt, $dtInfo);
            $daySet = $tmp->set;
            $daySetStart = $tmp->start;
            $daySetEnd = $tmp->end;
            $wNoMask = [];
            $wDayMaskRel = [];
            $timeSet = DateUtil::getTimeSet($rule, $dt);

            if ($freq >= Frequency::HOURLY) {
                if (
                    (!empty($byHour) && !in_array($hour, $byHour))
                    || ($freq >= Frequency::MINUTELY && ($byMinute !== null && count($byMinute)) && !in_array($minute, $byMinute))
                    || ($freq >= Frequency::SECONDLY && ($bySecond !== null && count($bySecond)) && !in_array($second, $bySecond))
                ) {
                    $timeSet = [];
                } else {
                    switch ($freq) {
                        case Frequency::HOURLY:
                            $timeSet = DateUtil::getTimeSetOfHour($rule, $dt);
                            break;
                        case Frequency::MINUTELY:
                            $timeSet = DateUtil::getTimeSetOfMinute($rule, $dt);
                            break;
                        case Frequency::SECONDLY:
                            $timeSet = DateUtil::getTimeSetOfSecond($dt);
                            break;
                    }
                }
            }

            // Handle byWeekNum
            if (!empty($byWeekNum)) {
                $no1WeekStart = $firstWeekStart = DateUtil::pymod(7 - $dtInfo->dayOfWeekYearDay1 + $weekStart, 7);

                if ($no1WeekStart >= 4) {
                    $no1WeekStart = 0;

                    $wYearLength = $dtInfo->yearLength + DateUtil::pymod(
                        $dtInfo->dayOfWeekYearDay1 - $weekStart,
                        7
                    );
                } else {
                    $wYearLength = $dtInfo->yearLength - $no1WeekStart;
                }

                $div = floor($wYearLength / 7);
                $mod = DateUtil::pymod($wYearLength, 7);
                $numWeeks = floor($div + ($mod / 4));

                foreach ($byWeekNum as $weekNum) {
                    if ($weekNum < 0) {
                        $weekNum += $numWeeks + 1;
                    }

                    if (!(0 < $weekNum && $weekNum <= $numWeeks)) {
                        continue;
                    }

                    if ($weekNum > 1) {
                        $offset = $no1WeekStart + ($weekNum - 1) * 7;
                        if ($no1WeekStart != $firstWeekStart) {
                            $offset -= 7 - $firstWeekStart;
                        }
                    } else {
                        $offset = $no1WeekStart;
                    }

                    for ($i = 0; $i < 7; ++$i) {
                        $wNoMask[] = $offset;
                        ++$offset;
                        if ($dtInfo->wDayMask[$offset] == $weekStart) {
                            break;
                        }
                    }
                }

                // Check week number 1 of next year as well
                if (in_array(1, $byWeekNum)) {
                    $offset = $no1WeekStart + $numWeeks * 7;

                    if ($no1WeekStart != $firstWeekStart) {
                        $offset -= 7 - $firstWeekStart;
                    }

                    // If week starts in next year, we don't care about it.
                    if ($offset < $dtInfo->yearLength) {
                        for ($k = 0; $k < 7; ++$k) {
                            $wNoMask[] = $offset;
                            ++$offset;
                            if ($dtInfo->wDayMask[$offset] == $weekStart) {
                                break;
                            }
                        }
                    }
                }

                if ($no1WeekStart) {
                    // Check last week number of last year as well.
                    // If $no1WeekStart is 0, either the year started on week start,
                    // or week number 1 got days from last year, so there are no
                    // days from last year's last week number in this year.
                    if (!in_array(-1, $byWeekNum)) {
                        $dtTmp = new \DateTime();
                        $dtTmp = $dtTmp->setDate((int) $year - 1, 1, 1);
                        $lastYearWeekDay = DateUtil::getDayOfWeek($dtTmp);
                        $lastYearNo1WeekStart = DateUtil::pymod(7 - $lastYearWeekDay + $weekStart, 7);
                        $lastYearLength = DateUtil::getYearLength($dtTmp);
                        if ($lastYearNo1WeekStart >= 4) {
                            $lastYearNo1WeekStart = 0;
                            $lastYearNumWeeks = floor(
                                52 + DateUtil::pymod(
                                    $lastYearLength + DateUtil::pymod(
                                        $lastYearWeekDay - $weekStart,
                                        7
                                    ),
                                    7
                                ) / 4
                            );
                        } else {
                            $lastYearNumWeeks = floor(
                                52 + DateUtil::pymod(
                                    $dtInfo->yearLength - $no1WeekStart,
                                    7
                                ) / 4
                            );
                        }
                    } else {
                        $lastYearNumWeeks = -1;
                    }

                    if (in_array($lastYearNumWeeks, $byWeekNum)) {
                        for ($i = 0; $i < $no1WeekStart; ++$i) {
                            $wNoMask[] = $i;
                        }
                    }
                }
            }

            // Handle relative weekdays (e.g. 3rd Friday of month)
            if (count($byWeekDayRel) > 0) {
                $ranges = [];

                if (Frequency::YEARLY == $freq) {
                    if (!empty($byMonth)) {
                        foreach ($byMonth as $mo) {
                            $ranges[] = array_slice($dtInfo->mRanges, $mo - 1, 2);
                        }
                    } else {
                        $ranges[] = [0, $dtInfo->yearLength];
                    }
                } elseif (Frequency::MONTHLY == $freq) {
                    $ranges[] = array_slice($dtInfo->mRanges, $month - 1, 2);
                }

                if (!empty($ranges)) {
                    foreach ($ranges as $range) {
                        $rangeStart = $range[0];
                        $rangeEnd = $range[1];
                        --$rangeEnd;

                        foreach ($byWeekDayRel as $weekday) {
                            if ($weekday->num < 0) {
                                $i = $rangeEnd + ($weekday->num + 1) * 7;
                                $i -= DateUtil::pymod(
                                    $dtInfo->wDayMask[$i] - $weekday->weekday,
                                    7
                                );
                            } else {
                                $i = $rangeStart + ($weekday->num - 1) * 7;
                                $i += DateUtil::pymod(
                                    7 - $dtInfo->wDayMask[$i] + $weekday->weekday,
                                    7
                                );
                            }

                            if ($rangeStart <= $i && $i <= $rangeEnd) {
                                $wDayMaskRel[] = $i;
                            }
                        }
                    }
                }
            }

            $numMatched = 0;
            foreach ($daySet as $i => $dayOfYear) {
                $dayOfMonth = $dtInfo->mDayMask[$dayOfYear];

                $ifByMonth = $byMonth !== null && !in_array($dtInfo->mMask[$dayOfYear], $byMonth);

                $ifByWeekNum = $byWeekNum !== null && !in_array($i, $wNoMask);

                $ifByYearDay = $byYearDay !== null && (
                    (
                        $i < $dtInfo->yearLength
                        && !in_array($i + 1, $byYearDay)
                        && !in_array(-$dtInfo->yearLength + $i, $byYearDay)
                    )
                    || (
                        $i >= $dtInfo->yearLength
                        && !in_array($i + 1 - $dtInfo->yearLength, $byYearDay)
                        && !in_array(-$dtInfo->nextYearLength + $i - $dtInfo->yearLength, $byYearDay)
                    )
                );

                $ifByMonthDay = $byMonthDay !== null && !in_array($dtInfo->mDayMask[$dayOfYear], $byMonthDay);

                // Handle "last day of next month" problem.
                if ($fixLastDayOfMonth
                    && $ifByMonthDay
                    && $implicitByMonthDay
                    && $startMonthLength > $dtInfo->monthLength
                    && $dayOfMonth == $dtInfo->monthLength
                    && $dayOfMonth < $startMonthLength
                    && !$numMatched
                ) {
                    $ifByMonthDay = false;
                }

                $ifByMonthDayNeg = count($byMonthDayNeg) > 0
                    && !in_array($dtInfo->mDayMaskNeg[$dayOfYear], $byMonthDayNeg);

                $ifByDay = count($byWeekDayInt) > 0 && !in_array($dtInfo->wDayMask[$dayOfYear], $byWeekDayInt);

                $ifWDayMaskRel = count($byWeekDayRel) > 0 && !in_array($dayOfYear, $wDayMaskRel);

                if ($byMonthDay !== null && count($byMonthDayNeg) > 0) {
                    if ($ifByMonthDay && $ifByMonthDayNeg) {
                        unset($daySet[$i]);
                    }
                } elseif ($ifByMonth || $ifByWeekNum || $ifByYearDay || $ifByMonthDay || $ifByMonthDayNeg || $ifByDay || $ifWDayMaskRel) {
                    unset($daySet[$i]);
                } else {
                    ++$numMatched;
                }
            }

            if (!empty($bySetPos) && !empty($daySet)) {
                $datesAdj = [];
                $tmpDaySet = array_combine($daySet, $daySet);

                foreach ($bySetPos as $setPos) {
                    if ($setPos < 0) {
                        $dayPos = (int) floor($setPos / count($timeSet));
                        $timePos = DateUtil::pymod($setPos, count($timeSet));
                    } else {
                        $dayPos = (int) floor(($setPos - 1) / count($timeSet));
                        $timePos = DateUtil::pymod($setPos - 1, count($timeSet));
                    }

                    $tmp = [];
                    for ($k = $daySetStart; $k <= $daySetEnd; ++$k) {
                        if (!array_key_exists($k, $tmpDaySet)) {
                            continue;
                        }

                        $tmp[] = $tmpDaySet[$k];
                    }

                    if ($dayPos < 0) {
                        $nextInSet = array_slice($tmp, $dayPos, 1);
                        if (count($nextInSet) === 0) {
                            continue;
                        }
                        $nextInSet = $nextInSet[0];
                    } else {
                        $nextInSet = $tmp[$dayPos] ?? null;
                    }

                    if (null !== $nextInSet) {
                        /** @var Time $time */
                        $time = $timeSet[$timePos];

                        $dtTmp = DateUtil::getDateTimeByDayOfYear(
                            $nextInSet,
                            (int) $dt->format('Y'),
                            $start->getTimezone()
                        );

                        $dtTmp = $dtTmp->setTime(
                            $time->hour,
                            $time->minute,
                            $time->second
                        );

                        $datesAdj[] = $dtTmp;
                    }
                }

                foreach ($datesAdj as $dtTmp) {
                    if ($until instanceof \DateTimeInterface && $dtTmp > $until) {
                        $continue = false;
                        break;
                    }

                    if ($dtTmp < $start) {
                        continue;
                    }

                    if ($constraint instanceof ConstraintInterface && !$constraint->test($dtTmp)) {
                        if (!$countConstraintFailures) {
                            if ($constraint->stopsTransformer()) {
                                $continue = false;
                                break;
                            } else {
                                continue;
                            }
                        }
                    } else {
                        $dates[$total] = $dtTmp;
                    }

                    if (null !== $count) {
                        --$count;
                        if ($count <= 0) {
                            $continue = false;
                            break;
                        }
                    }

                    ++$total;
                    if ($total > $vLimit) {
                        $continue = false;
                        break;
                    }
                }
            } else {
                foreach ($daySet as $dayOfYear) {
                    $dtTmp = DateUtil::getDateTimeByDayOfYear(
                        $dayOfYear,
                        (int) $dt->format('Y'),
                        $start->getTimezone()
                    );

                    foreach ($timeSet as $time) {
                        /** @var Time $time */
                        $dtTmp = $dtTmp->setTime(
                            $time->hour,
                            $time->minute,
                            $time->second
                        );

                        if ($until instanceof \DateTimeInterface && $dtTmp > $until) {
                            $continue = false;
                            break;
                        }

                        if ($dtTmp < $start) {
                            continue;
                        }

                        if ($constraint instanceof ConstraintInterface && !$constraint->test($dtTmp)) {
                            if (!$countConstraintFailures) {
                                if ($constraint->stopsTransformer()) {
                                    $continue = false;
                                    break;
                                } else {
                                    continue;
                                }
                            }
                        } else {
                            $dates[$total] = clone $dtTmp;
                        }

                        if (null !== $count) {
                            --$count;
                            if ($count <= 0) {
                                $continue = false;
                                break;
                            }
                        }

                        ++$total;
                        if ($total > $vLimit) {
                            $continue = false;
                            break;
                        }
                    }

                    if (!$continue) {
                        break;
                    }
                }

                if ($total > $vLimit) {
                    $continue = false;
                    break;
                }
            }

            switch ($freq) {
                case Frequency::YEARLY:
                    $year += $rule->getInterval();
                    $month = (int) $dt->format('n');
                    $dt = $dt->setDate((int) $year, $month, 1);

                    // Stop an infinite loop w/ a sane limit
                    ++$iterations;
                    if ($iterations > 300 && !count($dates)) {
                        break 2;
                    }
                    break;
                case Frequency::MONTHLY:
                    $month += $rule->getInterval();
                    if ($month > 12) {
                        $delta = floor($month / 12);
                        $mod = DateUtil::pymod($month, 12);
                        $month = $mod;
                        $year += $delta;
                        if ($month == 0) {
                            $month = 12;
                            --$year;
                        }
                    }
                    $dt = $dt->setDate((int) $year, $month, 1);
                    break;
                case Frequency::WEEKLY:
                    if ($weekStart > $dtInfo->dayOfWeek) {
                        $delta = ($dtInfo->dayOfWeek + 1 + (6 - $weekStart)) * -1 + $rule->getInterval() * 7;
                    } else {
                        $delta = ($dtInfo->dayOfWeek - $weekStart) * -1 + $rule->getInterval() * 7;
                    }

                    $dt = $dt->modify("+$delta day");
                    $year = (int) $dt->format('Y');
                    $month = (int) $dt->format('n');
                    break;
                case Frequency::DAILY:
                    $dt = $dt->modify('+'.$rule->getInterval().' day');
                    $year = (int) $dt->format('Y');
                    $month = (int) $dt->format('n');
                    break;
                case Frequency::HOURLY:
                    $dt = $dt->modify('+'.$rule->getInterval().' hours');
                    $year = (int) $dt->format('Y');
                    $month = (int) $dt->format('n');
                    $hour = (int) $dt->format('G');
                    break;
                case Frequency::MINUTELY:
                    $dt = $dt->modify('+'.$rule->getInterval().' minutes');
                    $year = (int) $dt->format('Y');
                    $month = (int) $dt->format('n');
                    $hour = (int) $dt->format('G');
                    $minute = (int) $dt->format('i');
                    break;
                case Frequency::SECONDLY:
                    $dt = $dt->modify('+'.$rule->getInterval().' seconds');
                    $year = (int) $dt->format('Y');
                    $month = (int) $dt->format('n');
                    $hour = (int) $dt->format('G');
                    $minute = (int) $dt->format('i');
                    $second = (int) $dt->format('s');
                    break;
            }
        }

        /** @var Recurrence[] $recurrences */
        $recurrences = [];
        foreach ($dates as $key => $start) {
            $end = clone $start;

            $recurrences[] = new Recurrence(
                start: $start,
                end: $end->add($durationInterval),
                index: $key
            );
        }

        $recurrences = $this->handleInclusions($rule->getRDates(), $recurrences);
        $recurrences = $this->handleExclusions($rule->getExDates(), $recurrences);

        return new RecurrenceCollection($recurrences);
    }

    /**
     * @param DateExclusion[]|null $exclusions
     * @param Recurrence[] $recurrences
     *
     * @return Recurrence[]
     */
    protected function handleExclusions(?array $exclusions, array $recurrences): array
    {
        if ($exclusions === null) {
            return \array_values($recurrences);
        }

        foreach ($exclusions as $exclusion) {
            $exclusionDate = $exclusion->date->format('Ymd');
            $exclusionTime = $exclusion->date->format('Ymd\THis');
            $exclusionTimezone = $exclusion->date->getTimezone();

            foreach ($recurrences as $key => $recurrence) {
                $recurrenceDate = $recurrence->getStart();

                if ($recurrenceDate->getTimezone()->getName() !== $exclusionTimezone->getName()) {
                    $recurrenceDate = clone $recurrenceDate;
                    $recurrenceDate = $recurrenceDate->setTimezone($exclusionTimezone);
                }

                if (!$exclusion->hasTime && $recurrenceDate->format('Ymd') == $exclusionDate) {
                    unset($recurrences[$key]);
                    continue;
                }

                if ($exclusion->hasTime && $recurrenceDate->format('Ymd\THis') == $exclusionTime) {
                    unset($recurrences[$key]);
                }
            }
        }

        return \array_values($recurrences);
    }

    /**
     * @param DateInclusion[]|null $inclusions
     * @param Recurrence[] $recurrences
     *
     * @return Recurrence[]
     */
    protected function handleInclusions(?array $inclusions, array $recurrences): array
    {
        if ($inclusions === null) {
            return \array_values($recurrences);
        }

        foreach ($inclusions as $inclusion) {
            $recurrence = new Recurrence(clone $inclusion->date, clone $inclusion->date);
            $recurrences[] = $recurrence;
        }

        return \array_values($recurrences);
    }
}

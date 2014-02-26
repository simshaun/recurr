<?php

/*
 * Copyright 2013 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Based on:
 * rrule.js - Library for working with recurrence rules for calendar dates.
 * Copyright 2010, Jakub Roztocil and Lars Schoning
 * https://github.com/jkbr/rrule/blob/master/LICENCE
 */

namespace Recurr;

use Recurr\Time;
use Recurr\Weekday;
use Recurr\DateUtil;
use Recurr\Exception\MissingData;

/**
 * This class is responsible for transforming a Rule in to an array
 * of \DateTime() objects.
 *
 * If a recurrence rule is infinitely recurring, a virtual limit is imposed.
 *
 * @package Recurr
 * @author  Shaun Simmons <shaun@envysphere.com>
 */
class RuleTransformer
{
    /** @var Rule */
    protected $rule;

    /** @var int */
    protected $virtualLimit;

    /** @var RuleTransformerConfig */
    protected $config;

    /**
     * Some versions of PHP are affected by a bug where
     * \DateTime::createFromFormat('z Y', ...) does not account for leap years.
     *
     * @var bool
     */
    protected $leapBug = false;

    /**
     * Construct a new RuleTransformer
     *
     * @param null                  $rule         The Rule
     * @param null                  $virtualLimit The virtual limit imposed upon infinite recurrence
     * @param RuleTransformerConfig $config
     */
    public function __construct($rule = null, $virtualLimit = null, RuleTransformerConfig $config = null)
    {
        if (null !== $rule) {
            $this->setRule($rule);
        }

        if (is_int($virtualLimit)) {
            $this->setVirtualLimit($virtualLimit);
        }

        if (!$config instanceof RuleTransformerConfig) {
            $config = new RuleTransformerConfig();
        }

        $this->config = $config;

        $this->leapBug = DateUtil::hasLeapYearBug();
    }

    /**
     * @param RuleTransformerConfig $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Transform a Rule in to an array of \DateTimes
     *
     * @return array
     * @throws MissingData
     */
    public function getComputedArray()
    {
        $rule = $this->getRule();
        if (null === $rule) {
            throw new MissingData('Rule has not been set');
        }

        $start = $rule->getStartDate();
        $until = $rule->getUntil();

        if (null === $start) {
            $start = new \DateTime(
                'now',
                $until instanceof \DateTime ? $until->getTimezone() : null
            );
        }

        $startDay          = $start->format('j');
        $startMonthLength  = $start->format('t');
        $fixLastDayOfMonth = false;

        $dt = clone $start;

        $maxCount = $rule->getCount();
        $vLimit   = $this->getVirtualLimit($rule);

        $freq          = $rule->getFreq();
        $weekStart     = $rule->getWeekStartAsNum();
        $bySecond      = $rule->getBySecond();
        $byMinute      = $rule->getByMinute();
        $byHour        = $rule->getByHour();
        $byMonth       = $rule->getByMonth();
        $byWeekNum     = $rule->getByWeekNumber();
        $byYearDay     = $rule->getByYearDay();
        $byMonthDay    = $rule->getByMonthDay();
        $byMonthDayNeg = array();
        $byWeekDay     = $rule->getByDayTransformedToWeekdays();
        $byWeekDayRel  = array();
        $bySetPos      = $rule->getBySetPosition();

        if (!(!empty($byWeekNum) || !empty($byYearDay) || !empty($byMonthDay) || !empty($byWeekDay))) {
            switch ($freq) {
                case Frequency::YEARLY:
                    if (empty($byMonth)) {
                        $byMonth = array($start->format('n'));
                    }

                    $byMonthDay = array($startDay);
                    break;
                case Frequency::MONTHLY:
                    if ($startDay > 28) {
                        $fixLastDayOfMonth = true;
                    }

                    $byMonthDay = array($startDay);
                    break;
                case Frequency::WEEKLY:
                    $byWeekDay = array(
                        new Weekday(
                            DateUtil::getDayOfWeek($start),
                            null
                        )
                    );
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

        if (!empty($byWeekDay)) {
            foreach ($byWeekDay as $idx => $day) {
                /** @var $day Weekday */

                if (!empty($day->num)) {
                    $byWeekDayRel[] = $day;
                    unset($byWeekDay[$idx]);
                } else {
                    $byWeekDay[$idx] = $day->weekday;
                }
            }
        }

        if (empty($byYearDay)) {
            $byYearDay = null;
        }

        if (empty($byMonthDay)) {
            $byMonthDay = null;
        }

        if (empty($byMonthDayNeg)) {
            $byMonthDayNeg = null;
        }

        if (empty($byWeekDay)) {
            $byWeekDay = null;
        }

        if (!count($byWeekDayRel)) {
            $byWeekDayRel = null;
        }

        $year   = $dt->format('Y');
        $month  = $dt->format('n');
        $day    = $dt->format('j');
        $hour   = $dt->format('G');
        $minute = $dt->format('i');
        $second = $dt->format('s');

        $dates    = array();
        $total    = 1;
        $count    = $maxCount;
        $continue = true;
        while ($continue) {
            $dtInfo      = DateUtil::getDateInfo($dt);

            $tmp         = DateUtil::getDaySet($rule, $dt, $dtInfo, $start);
            $daySet      = $tmp->set;
            $daySetStart = $tmp->start;
            $daySetEnd   = $tmp->end;
            $wNoMask     = array();
            $wDayMaskRel = array();
            $timeSet     = DateUtil::getTimeSet($rule, $dt);

            if ($freq >= Frequency::HOURLY) {
                if (($freq >= Frequency::HOURLY   && !empty($byHour)   && !in_array($hour, $byHour)) ||
                    ($freq >= Frequency::MINUTELY && !empty($byMinute) && !in_array($minute, $byMinute)) ||
                    ($freq >= Frequency::SECONDLY && !empty($bySecond) && !in_array($second, $bySecond)))
                {
                    $timeSet = array();
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
                $no1WeekStart = $firstWeekStart =
                    DateUtil::pymod(7 - $dtInfo->dayOfWeekYearDay1 + $weekStart, 7);

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

                    for ($i = 0; $i < 7; $i++) {
                        $wNoMask[] = $offset;
                        $offset++;
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
                        for ($k = 0; $k < 7; $k++) {
                            $wNoMask[] = $offset;
                            $offset += 1;
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
                        $dtTmp->setDate($year - 1, 1, 1);
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
                        for ($i = 0; $i < $no1WeekStart; $i++) {
                            $wNoMask[] = $i;
                        }
                    }
                }
            }

            // Handle relative weekdays (e.g. 3rd Friday of month)
            if (!empty($byWeekDayRel)) {
                $ranges = array();

                if (Frequency::YEARLY == $freq) {
                    if (!empty($byMonth)) {
                        foreach ($byMonth as $mo) {
                            $ranges[] = array_slice($dtInfo->mRanges, $mo - 1, 2);
                        }
                    } else {
                        $ranges[] = array(0, $dtInfo->yearLength);
                    }
                } elseif (Frequency::MONTHLY == $freq) {
                    $ranges[] = array_slice($dtInfo->mRanges, $month - 1, 2);
                }

                if (!empty($ranges)) {
                    foreach ($ranges as $range) {
                        $rangeStart = $range[0];
                        $rangeEnd   = $range[1];
                        --$rangeEnd;

                        reset($byWeekDayRel);
                        foreach ($byWeekDayRel as $weekday) {
                            /** @var Weekday $weekday */

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

            foreach ($daySet as $i => $dayOfYear) {
                $ifByMonth = $byMonth !== null && !in_array(
                    $dtInfo->mMask[$dayOfYear],
                    $byMonth
                );

                $ifByWeekNum = $byWeekNum !== null && !in_array(
                    $i,
                    $wNoMask
                );

                $ifByYearDay =
                    $byYearDay !== null && (($i < $dtInfo->yearLength && !in_array(
                        $i + 1,
                        $byYearDay
                    ) && !in_array(
                        -$dtInfo->yearLength + $i,
                        $byYearDay
                    )) || ($i >= $dtInfo->yearLength && !in_array(
                        $i + 1 - $dtInfo->yearLength,
                        $byYearDay
                    ) && !in_array(
                        -$dtInfo->nextYearLength + $i - $dtInfo->yearLength,
                        $byYearDay
                    )));

                $ifByMonthDay = $byMonthDay !== null && !in_array(
                    $dtInfo->mDayMask[$dayOfYear],
                    $byMonthDay
                );

                if ($ifByMonthDay && $fixLastDayOfMonth && $i < $startMonthLength && $i == $dtInfo->monthLength) {
                    $ifByMonthDay = false;
                }

                $ifByMonthDayNeg = $byMonthDayNeg !== null && !in_array(
                    $dtInfo->mDayMaskNeg[$dayOfYear],
                    $byMonthDayNeg
                );

                $ifByDay = $byWeekDay !== null && count($byWeekDay) && !in_array(
                    $dtInfo->wDayMask[$dayOfYear],
                    $byWeekDay
                );

                $ifWDayMaskRel =
                    $byWeekDayRel !== null && !in_array($dayOfYear, $wDayMaskRel);

                if ($byMonthDay !== null && $byMonthDayNeg !== null) {
                    if ($ifByMonthDay && $ifByMonthDayNeg) {
                        unset($daySet[$i]);
                    }
                } elseif ($ifByMonth || $ifByWeekNum || $ifByYearDay || $ifByMonthDay || $ifByMonthDayNeg || $ifByDay || $ifWDayMaskRel) {
                    unset($daySet[$i]);
                }
            }

            if (!empty($bySetPos)) {
                $datesAdj  = array();
                $tmpDaySet = array_combine($daySet, $daySet);

                foreach ($bySetPos as $setPos) {
                    if ($setPos < 0) {
                        $dayPos  = floor($setPos / count($timeSet));
                        $timePos = DateUtil::pymod($setPos, count($timeSet));
                    } else {
                        $dayPos = floor(($setPos - 1) / count($timeSet));
                        $timePos = DateUtil::pymod(($setPos - 1), count($timeSet));
                    }

                    $tmp = array();
                    for ($k = $daySetStart; $k <= $daySetEnd; $k++) {
                        if (!array_key_exists($k, $tmpDaySet)) {
                            continue;
                        }

                        $tmp[] = $tmpDaySet[$k];
                    }

                    if ($dayPos < 0) {
                        $nextInSet = array_slice($tmp, $dayPos, 1);
                        $nextInSet = $nextInSet[0];
                    } else {
                        $nextInSet = $tmp[$dayPos];
                    }

                    /** @var Time $time */
                    $time = $timeSet[$timePos];

                    $dtTmp =
                        DateUtil::getDateTimeByDayOfYear($nextInSet, $dt->format('Y'), $start->getTimezone());

                    $dtTmp->setTime(
                        $time->hour,
                        $time->minute,
                        $time->second
                    );

                    $datesAdj[] = $dtTmp;
                }

                foreach ($datesAdj as $dtTmp) {
                    if (null !== $until && $dtTmp > $until) {
                        $continue = false;
                        break;
                    } elseif ($dtTmp >= $start) {
                        $dates[] = $dtTmp;

                        if (null !== $count) {
                            --$count;
                            if ($count <= 0) {
                                $continue = false;
                                break;
                            }
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
                    $dtTmp = DateUtil::getDateTimeByDayOfYear($dayOfYear, $dt->format('Y'), $start->getTimezone());

                    foreach ($timeSet as $time) {
                        /** @var Time $time */
                        $dtTmp->setTime(
                            $time->hour,
                            $time->minute,
                            $time->second
                        );

                        if (null !== $until && $dtTmp > $until) {
                            $continue = false;
                            break;
                        } elseif ($dtTmp >= $start) {
                            $dates[] = clone $dtTmp;

                            if (null !== $count) {
                                --$count;
                                if ($count <= 0) {
                                    $continue = false;
                                    break;
                                }
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
                    $month = $dt->format('n');
                    $day   = $dt->format('j');
                    $dt->setDate($year, $month, $day);
                    break;
                case Frequency::MONTHLY:
                    $month += $rule->getInterval();
                    if ($month > 12) {
                        $delta = floor($month / 12);
                        $mod   = DateUtil::pymod($month, 12);
                        $month = $mod;
                        $year += $delta;
                        if ($month == 0) {
                            $month = 12;
                            --$year;
                        }
                    }
                    $dt->setDate($year, $month, 1);
                    break;
                case Frequency::WEEKLY:
                    if ($weekStart > $dtInfo->dayOfWeek) {
                        $delta = ($dtInfo->dayOfWeek + 1 + (6 - $weekStart)) * -1 +
                            $rule->getInterval() * 7;
                    } else {
                        $delta = ($dtInfo->dayOfWeek - $weekStart) * -1 +
                            $rule->getInterval() * 7;
                    }

                    $dt->modify("+$delta day");
                    $year  = $dt->format('Y');
                    $month = $dt->format('n');
                    $day   = $dt->format('j');
                    break;
                case Frequency::DAILY:
                    $dt->modify('+'.$rule->getInterval().' day');
                    $year  = $dt->format('Y');
                    $month = $dt->format('n');
                    $day   = $dt->format('j');
                    break;
                case Frequency::HOURLY:
                    $dt->modify('+'.$rule->getInterval().' hours');
                    $year  = $dt->format('Y');
                    $month = $dt->format('n');
                    $day   = $dt->format('j');
                    $hour  = $dt->format('G');
                    break;
                case Frequency::MINUTELY:
                    $dt->modify('+'.$rule->getInterval().' minutes');
                    $year   = $dt->format('Y');
                    $month  = $dt->format('n');
                    $day    = $dt->format('j');
                    $hour   = $dt->format('G');
                    $minute = $dt->format('i');
                    break;
                case Frequency::SECONDLY:
                    $dt->modify('+'.$rule->getInterval().' seconds');
                    $year   = $dt->format('Y');
                    $month  = $dt->format('n');
                    $day    = $dt->format('j');
                    $hour   = $dt->format('G');
                    $minute = $dt->format('i');
                    $second = $dt->format('s');
                    break;
            }
        }

        return $dates;
    }

    /**
     * Set the Rule
     *
     * @param Rule $rule The Rule
     *
     * @return $this
     */
    public function setRule($rule)
    {
        $this->rule = $rule;

        return $this;
    }

    /**
     * Get the Rule
     *
     * @return Rule
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Set the virtual limit imposed upon infinitely recurring events.
     *
     * @param int $virtualLimit The limit
     *
     * @return $this
     */
    public function setVirtualLimit($virtualLimit)
    {
        $this->virtualLimit = (int) $virtualLimit;

        return $this;
    }

    /**
     * Get the virtual limit imposed upon infinitely recurring events.
     *
     * @param Rule $rule
     *
     * @return int
     */
    public function getVirtualLimit()
    {
        return $this->virtualLimit;
    }
}

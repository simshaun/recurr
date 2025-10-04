<?php

/*
 * Copyright 2025 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Based on rrule.js
 * Copyright 2010, Jakub Roztocil and Lars Schoning
 * https://github.com/jkbr/rrule/blob/master/LICENCE
 *
 * Based on python-dateutil - Extensions to the standard Python datetime module.
 * Copyright (c) 2003-2011 - Gustavo Niemeyer <gustavo@niemeyer.net>
 * Copyright (c) 2012 - Tomi Pieviläinen <tomi.pievilainen@iki.fi>
 */

namespace Recurr;

/**
 * Utility class providing helper methods for RRULE transformation.
 *
 * Contains static methods for generating date/time masks, day sets, and performing
 * calendar calculations needed during recurrence evaluation.
 *
 * @author Shaun Simmons <gh@simshaun.com>
 */
class DateUtil
{
    /**
     * Day-of-year values for the end of each month in leap years (366 days).
     *
     * @var int[]
     */
    public static array $monthEndDoY366 = [
        0, 31, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335, 366,
    ];

    /**
     * Day-of-year values for the end of each month in non-leap years (365 days).
     *
     * @var int[]
     */
    public static array $monthEndDoY365 = [
        0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334, 365,
    ];

    /**
     * Repeating pattern of day-of-week values (0-6) for up to 373 days, 0=Monday, 6=Sunday.
     *
     * Used as a template that gets sliced based on the year's starting weekday.
     *
     * @var array<int, int>
     */
    public static array $wDayMask = [
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6,
        0, 1, 2, 3, 4, 5, 6,
    ];

    public static function getDateInfo(\DateTime|\DateTimeImmutable $dt): DateInfo
    {
        $i = new DateInfo();
        $i->dt = $dt;
        $i->dayOfWeek = self::getDayOfWeek($dt);
        $i->monthLength = (int) $dt->format('t');
        $i->yearLength = self::getYearLength($dt);

        $i->mMask = self::getMonthMask($dt);
        $i->mDayMask = self::getMonthDaysMask($dt);
        $i->mDayMaskNeg = self::getMonthDaysMask($dt, true);

        $i->mRanges = $i->yearLength == 365 ? self::$monthEndDoY365 : self::$monthEndDoY366;

        $tmpDt = clone $dt;
        $tmpDt = $tmpDt->setDate((int) $dt->format('Y') + 1, 1, 1);
        $i->nextYearLength = self::getYearLength($tmpDt);

        $tmpDt = clone $dt;
        $tmpDt = $tmpDt->setDate((int) $dt->format('Y'), 1, 1);
        $i->dayOfWeekYearDay1 = self::getDayOfWeek($tmpDt);

        $i->wDayMask = array_slice(
            self::$wDayMask,
            $i->dayOfWeekYearDay1
        );

        return $i;
    }

    public static function getDaySet(Rule $rule, \DateTime|\DateTimeImmutable $dt, DateInfo $dtInfo): DaySet
    {
        return match ($rule->getFreq()) {
            Frequency::SECONDLY, Frequency::MINUTELY, Frequency::HOURLY, Frequency::DAILY => self::getDaySetOfDay($dt),
            Frequency::WEEKLY => self::getDaySetOfWeek($dt, $rule, $dtInfo),
            Frequency::MONTHLY => self::getDaySetOfMonth($dt),
            Frequency::YEARLY => self::getDaySetOfYear($dt),
            default => throw new \RuntimeException('Invalid freq.'),
        };
    }

    public static function getDaySetOfYear(\DateTime|\DateTimeImmutable $dt): DaySet
    {
        $yearLen = self::getYearLength($dt);
        $set = range(0, $yearLen - 1);

        return new DaySet(set: $set, start: 0, end: $yearLen);
    }

    public static function getDaySetOfMonth(\DateTime|\DateTimeImmutable $dt): DaySet
    {
        $dateInfo = self::getDateInfo($dt);
        $monthNum = (int) $dt->format('n');

        $start = $dateInfo->mRanges[$monthNum - 1];
        $end = $dateInfo->mRanges[$monthNum];

        $days = range(0, (int) $dt->format('t') - 1);
        $set = range($start, $end - 1);
        $set = array_combine($days, $set);

        return new DaySet(set: $set, start: $start, end: $end - 1);
    }

    public static function getDaySetOfWeek(
        \DateTime|\DateTimeImmutable $dt,
        ?Rule $rule = null,
        ?DateInfo $dtInfo = null,
    ): DaySet {
        $yearStart = clone $dt;
        $yearStart = $yearStart->setDate((int) $yearStart->format('Y'), 1, 1);

        $diff = $dt->diff($yearStart);
        $startDayOfYear = (int) $diff->days;

        $set = [];
        for ($dayOfYear = $startDayOfYear, $k = 0; $k < 7; ++$k) {
            $set[] = $dayOfYear;
            ++$dayOfYear;

            if ($dtInfo instanceof DateInfo && $rule instanceof Rule && $dtInfo->wDayMask[$dayOfYear] === $rule->getWeekStartAsNum()) {
                break;
            }
        }

        return new DaySet(set: $set, start: $startDayOfYear, end: $dayOfYear);
    }

    public static function getDaySetOfDay(\DateTime|\DateTimeImmutable $dt): DaySet
    {
        $dayOfYear = (int) $dt->format('z');

        if (self::isLeapYearDate($dt) && self::hasLeapYearBug() && (int) $dt->format('nj') > 229) {
            --$dayOfYear;
        }

        $start = $dayOfYear;
        $end = $dayOfYear;

        $set = range($start, $end);

        return new DaySet(set: $set, start: $start, end: $end + 1);
    }

    /**
     * @return Time[]
     */
    public static function getTimeSetOfHour(Rule $rule, \DateTime|\DateTimeImmutable $dt): array
    {
        $set = [];

        $hour = (int) $dt->format('G');
        $byMinute = $rule->getByMinute();
        $bySecond = $rule->getBySecond();

        if (empty($byMinute)) {
            $byMinute = [(int) $dt->format('i')];
        }

        if (empty($bySecond)) {
            $bySecond = [(int) $dt->format('s')];
        }

        foreach ($byMinute as $minute) {
            foreach ($bySecond as $second) {
                $set[] = new Time($hour, $minute, $second);
            }
        }

        return $set;
    }

    /**
     * @return Time[]
     */
    public static function getTimeSetOfMinute(Rule $rule, \DateTime|\DateTimeImmutable $dt): array
    {
        $set = [];

        $hour = (int) $dt->format('G');
        $minute = (int) $dt->format('i');
        $bySecond = $rule->getBySecond();

        if (empty($bySecond)) {
            $bySecond = [(int) $dt->format('s')];
        }

        foreach ($bySecond as $second) {
            $set[] = new Time($hour, $minute, $second);
        }

        return $set;
    }

    /**
     * @return Time[]
     */
    public static function getTimeSetOfSecond(\DateTime|\DateTimeImmutable $dt): array
    {
        return [new Time((int) $dt->format('G'), (int) $dt->format('i'), (int) $dt->format('s'))];
    }

    /**
     * @return Time[]
     */
    public static function getTimeSet(Rule $rule, \DateTime|\DateTimeImmutable $dt): array
    {
        $set = [];

        if ($rule->getFreq() >= Frequency::HOURLY) {
            return $set;
        }

        $byHour = $rule->getByHour();
        $byMinute = $rule->getByMinute();
        $bySecond = $rule->getBySecond();

        if (empty($byHour)) {
            $byHour = [(int) $dt->format('G')];
        }

        if (empty($byMinute)) {
            $byMinute = [(int) $dt->format('i')];
        }

        if (empty($bySecond)) {
            $bySecond = [(int) $dt->format('s')];
        }

        foreach ($byHour as $hour) {
            foreach ($byMinute as $minute) {
                foreach ($bySecond as $second) {
                    $set[] = new Time($hour, $minute, $second);
                }
            }
        }

        return $set;
    }

    /**
     * Generate a lookup array mapping day-of-year index to day-of-month.
     *
     * @param bool $negative If true, returns negative day-of-month values (-31 to -1)
     *
     * @return array<int, int> Day-of-month values (1-31 or -31 to -1) indexed by day-of-year
     */
    public static function getMonthDaysMask(\DateTime|\DateTimeImmutable $dt, bool $negative = false): array
    {
        if ($negative) {
            $m29 = range(-29, -1);
            $m30 = range(-30, -1);
            $m31 = range(-31, -1);
        } else {
            $m29 = range(1, 29);
            $m30 = range(1, 30);
            $m31 = range(1, 31);
        }

        $mask = array_merge(
            $m31, // Jan (31)
            $m29, // Feb (28)
            $m31, // Mar (31)
            $m30, // Apr (30)
            $m31, // May (31)
            $m30, // Jun (30)
            $m31, // Jul (31)
            $m31, // Aug (31)
            $m30, // Sep (30)
            $m31, // Oct (31)
            $m30, // Nov (30)
            $m31, // Dec (31)
            array_slice($m31, 0, 7)
        );

        if (self::isLeapYearDate($dt)) {
            return $mask;
        }

        if ($negative) {
            $mask = array_merge(array_slice($mask, 0, 31), array_slice($mask, 32));
        } else {
            $mask = array_merge(array_slice($mask, 0, 59), array_slice($mask, 60));
        }

        return $mask;
    }

    /**
     * Generate a lookup array mapping day-of-year index to month number.
     *
     * @return array<int, int> Month numbers (1-12) indexed by day-of-year
     */
    public static function getMonthMask(\DateTime|\DateTimeImmutable $dt): array
    {
        if (self::isLeapYearDate($dt)) {
            return array_merge(
                array_fill(0, 31, 1), // Jan (31)
                array_fill(0, 29, 2), // Feb (29)
                array_fill(0, 31, 3), // Mar (31)
                array_fill(0, 30, 4), // Apr (30)
                array_fill(0, 31, 5), // May (31)
                array_fill(0, 30, 6), // Jun (30)
                array_fill(0, 31, 7), // Jul (31)
                array_fill(0, 31, 8), // Aug (31)
                array_fill(0, 30, 9), // Sep (30)
                array_fill(0, 31, 10), // Oct (31)
                array_fill(0, 30, 11), // Nov (30)
                array_fill(0, 31, 12), // Dec (31)
                array_fill(0, 7, 1)
            );
        } else {
            return array_merge(
                array_fill(0, 31, 1), // Jan (31)
                array_fill(0, 28, 2), // Feb (28)
                array_fill(0, 31, 3), // Mar (31)
                array_fill(0, 30, 4), // Apr (30)
                array_fill(0, 31, 5), // May (31)
                array_fill(0, 30, 6), // Jun (30)
                array_fill(0, 31, 7), // Jul (31)
                array_fill(0, 31, 8), // Aug (31)
                array_fill(0, 30, 9), // Sep (30)
                array_fill(0, 31, 10), // Oct (31)
                array_fill(0, 30, 11), // Nov (30)
                array_fill(0, 31, 12), // Dec (31)
                array_fill(0, 7, 1)
            );
        }
    }

    public static function getDateTimeByDayOfYear(
        int $dayOfYear,
        int $year,
        \DateTimeZone $timezone,
    ): \DateTime {
        $dtTmp = new \DateTime('now', $timezone);
        $dtTmp = $dtTmp->setDate($year, 1, 1);

        return $dtTmp->modify("+$dayOfYear day");
    }

    public static function hasLeapYearBug(): bool
    {
        $leapBugTest = \DateTime::createFromFormat('Y-m-d', '2016-03-21');
        if ($leapBugTest === false) {
            return false;
        }

        return $leapBugTest->format('z') != '80';
    }

    /**
     * closure/goog/math/math.js:modulo
     * Copyright 2006 The Closure Library Authors.
     *
     * The % operator in PHP returns the remainder of a / b, but differs from
     * some other languages in that the result will have the same sign as the
     * dividend. For example, -1 % 8 == -1, whereas in some other languages
     * (such as Python) the result would be 7. This function emulates the more
     * correct modulo behavior, which is useful for certain applications such as
     * calculating an offset index in a circular list.
     *
     * @param int $a the dividend
     * @param int $b the divisor
     *
     * @return int $a % $b where the result is between 0 and $b
     *             (either 0 <= x < $b
     *             or $b < x <= 0, depending on the sign of $b)
     */
    public static function pymod(int $a, int $b): int
    {
        $x = $a % $b;

        // If $x and $b differ in sign, add $b to wrap the result to the correct sign.
        return ($x * $b < 0) ? $x + $b : $x;
    }

    public static function isLeapYearDate(\DateTime|\DateTimeImmutable $dt): bool
    {
        return (bool) $dt->format('L');
    }

    public static function isLeapYear(int $year): bool
    {
        $isDivisBy4 = $year % 4 == 0;
        $isDivisBy100 = $year % 100 == 0;
        $isDivisBy400 = $year % 400 == 0;

        // http://en.wikipedia.org/wiki/February_29
        if ($isDivisBy100 && !$isDivisBy400) {
            return false;
        }

        return $isDivisBy4;
    }

    /**
     * Get the day of the week as a two-letter code (MO-SU).
     *
     * @return string MO, TU, WE, TH, FR, SA, or SU
     */
    public static function getDayOfWeekAsText(\DateTime|\DateTimeImmutable $dt): string
    {
        $dayOfWeek = $dt->format('w') - 1;

        if ($dayOfWeek < 0) {
            $dayOfWeek = 6;
        }

        $map = ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'];

        return $map[$dayOfWeek];
    }

    /**
     * Get the day of the week as an integer (0-6).
     *
     * @return int 0=Monday, 6=Sunday
     */
    public static function getDayOfWeek(\DateTime|\DateTimeImmutable $dt): int
    {
        $dayOfWeek = ((int) $dt->format('w')) - 1;

        if ($dayOfWeek < 0) {
            $dayOfWeek = 6;
        }

        return $dayOfWeek;
    }

    /**
     * Get the number of days in a year (365 or 366).
     */
    public static function getYearLength(\DateTime|\DateTimeImmutable $dt): int
    {
        return self::isLeapYearDate($dt) ? 366 : 365;
    }
}

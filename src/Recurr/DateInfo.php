<?php

/*
 * Copyright 2013 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Based on rrule.js
 * Copyright 2010, Jakub Roztocil and Lars Schoning
 * https://github.com/jkbr/rrule/blob/master/LICENCE
 */

namespace Recurr;

use DateTimeInterface;

/**
 * Class DateInfo is responsible for holding information based on a particular
 * date that is applicable to a Rule.
 *
 * @package Recurr
 * @author  Shaun Simmons <shaun@envysphere.com>
 */
class DateInfo
{
    public DateTimeInterface $dt;

    /**
     * @var int Number of days in the month.
     */
    public int $monthLength;

    /**
     * @var int Number of days in the year (365 normally, 366 on leap years)
     */
    public int $yearLength;

    /**
     * @var int Number of days in the next year (365 normally, 366 on leap years)
     */
    public int $nextYearLength;

    /**
     * @var array Day of year of last day of each month.
     */
    public array $mRanges;

    /** @var int Day of week */
    public int $dayOfWeek;

    /** @var int Day of week of the year's first day */
    public int $dayOfWeekYearDay1;

    /**
     * @var array Month number for each day of the year.
     */
    public array $mMask;

    /**
     * @var array Month-daynumber for each day of the year.
     */
    public array $mDayMask;

    /**
     * @var array Month-daynumber for each day of the year (in reverse).
     */
    public array $mDayMaskNeg;

    /**
     * @var array Day of week (0-6) for each day of the year, 0 being Monday
     */
    public array $wDayMask;
}

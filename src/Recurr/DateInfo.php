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
 */

namespace Recurr;

/**
 * Contextual information about a date used during RRULE transformation.
 *
 * Provides lookup masks and metadata to efficiently determine which days satisfy rule constraints.
 *
 * @author Shaun Simmons <gh@simshaun.com>
 */
class DateInfo
{
    public \DateTime|\DateTimeImmutable $dt;

    /**
     * @var int Number of days in the current month
     */
    public int $monthLength;

    /**
     * @var int Number of days in the current year (365 or 366 for leap years)
     */
    public int $yearLength;

    /**
     * @var int Number of days in the following year (365 or 366 for leap years)
     */
    public int $nextYearLength;

    /**
     * @var array<int, int> Day-of-year index for the end of each month (0-indexed array)
     */
    public array $mRanges;

    /**
     * @var int Day of week (0-6) for the current date, 0=Monday, 6=Sunday
     */
    public int $dayOfWeek;

    /**
     * @var int Day of week (0-6) for January 1st of the current year
     */
    public int $dayOfWeekYearDay1;

    /**
     * @var array<int, int> Maps day-of-year index to month number (1-12)
     */
    public array $mMask;

    /**
     * @var array<int, int> Maps day-of-year index to day-of-month (1-31)
     */
    public array $mDayMask;

    /**
     * @var array<int, int> Maps day-of-year index to negative day-of-month (-31 to -1, where -1 is last day)
     */
    public array $mDayMaskNeg;

    /**
     * @var array<int, int> Maps day-of-year index to day-of-week (0-6), 0=Monday, 6=Sunday
     */
    public array $wDayMask;
}

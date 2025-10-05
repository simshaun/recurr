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

use Recurr\Exception\InvalidWeekday;

/**
 * Represents a day of the week, optionally with positional modifier for BYDAY rule part.
 *
 * Can represent either a simple weekday (MO, TU) or a positional weekday (1MO, -1FR).
 * Positional weekdays are used with MONTHLY and YEARLY frequencies to specify occurrences
 * like "first Monday" (1MO) or "last Friday" (-1FR).
 *
 * @author Shaun Simmons <gh@simshaun.com>
 */
class Weekday implements \Stringable
{
    /**
     * Weekday number.
     *
     * 0 = Sunday
     * 1 = Monday
     * 2 = Tuesday
     * 3 = Wednesday
     * 4 = Thursday
     * 5 = Friday
     * 6 = Saturday
     */
    public int $weekday;

    /**
     * @var string[] Two-letter weekday codes indexed by weekday number (0-6)
     */
    protected static array $days = ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'];

    /**
     * @param int|string $weekday Weekday as integer (0-6) or two-letter code (MO, TU, WE, TH, FR, SA, SU)
     * @param int|null $num Position modifier for MONTHLY/YEARLY frequencies (e.g., 1 = first, -1 = last, null = any)
     *
     * @throws InvalidWeekday
     */
    public function __construct(int|string $weekday, public ?int $num)
    {
        if (is_numeric($weekday) && $weekday > 6 || $weekday < 0) {
            throw new InvalidWeekday('Day is not a valid weekday (0-6)');
        } elseif (!is_numeric($weekday) && !in_array($weekday, static::$days)) {
            throw new InvalidWeekday('Day is not a valid weekday (SU, MO, ...)');
        }

        if (!is_numeric($weekday)) {
            $weekday = array_search($weekday, static::$days);
        }

        $this->weekday = (int) $weekday;
    }

    public function __toString(): string
    {
        return $this->num.static::$days[$this->weekday];
    }
}

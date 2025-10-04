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
 * Represents a set of day-of-year values for a given time period (day, week, month, or year).
 *
 * Used during RRULE transformation to determine which days in a period should be evaluated
 * for potential occurrences based on the rule's frequency.
 *
 * @author Shaun Simmons <gh@simshaun.com>
 */
class DaySet
{
    /**
     * @param int[] $set Array of day-of-year indices (0-365) representing each day in the period
     * @param int $start Day-of-year index where the period starts
     * @param int $end Day-of-year index where the period ends
     */
    public function __construct(
        public array $set,
        public int $start,
        public int $end,
    ) {}
}

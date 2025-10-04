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
 * Represents a time of day (hour, minute, second) without date context.
 *
 * Used during RRULE transformation to generate all time combinations for a given day based on
 * BYHOUR, BYMINUTE, and BYSECOND rule parts.
 *
 * @author Shaun Simmons <gh@simshaun.com>
 */
class Time
{
    /**
     * @param int $hour Hour in 24-hour format (0-23)
     * @param int $minute Minute (0-59)
     * @param int $second Second (0-59)
     */
    public function __construct(
        public int $hour,
        public int $minute,
        public int $second,
    ) {}
}

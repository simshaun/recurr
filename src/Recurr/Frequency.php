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

namespace Recurr;

class Frequency
{
    public const int YEARLY = 0;
    public const int MONTHLY = 1;
    public const int WEEKLY = 2;
    public const int DAILY = 3;
    public const int HOURLY = 4;
    public const int MINUTELY = 5;
    public const int SECONDLY = 6;
}

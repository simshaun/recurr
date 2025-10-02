<?php

/*
 * Copyright 2014 Shaun Simmons
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
    public const YEARLY = 0;
    public const MONTHLY = 1;
    public const WEEKLY = 2;
    public const DAILY = 3;
    public const HOURLY = 4;
    public const MINUTELY = 5;
    public const SECONDLY = 6;
}

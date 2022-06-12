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

/**
 * Class Time is a storage container for a time of day.
 *
 * @package Recurr
 * @author  Shaun Simmons <shaun@envysphere.com>
 */
class Time
{
    public int $hour;

    public int $minute;

    public int $second;

    public function __construct(int $hour, int $minute, int $second)
    {
        $this->hour   = $hour;
        $this->minute = $minute;
        $this->second = $second;
    }
}

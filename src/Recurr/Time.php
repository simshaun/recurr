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
 * Class Time is a storage container for a time of day.
 *
 * @author  Shaun Simmons <gh@simshaun.com>
 */
class Time
{
    /**
     * @param int $hour
     * @param int $minute
     * @param int $second
     */
    public function __construct(public $hour, public $minute, public $second) {}
}

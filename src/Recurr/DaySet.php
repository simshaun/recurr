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
 * Class DaySet is a container for a set and its meta.
 *
 * @author  Shaun Simmons <gh@simshaun.com>
 */
class DaySet
{
    /**
     * Constructor
     *
     * @param array $set Set of days
     * @param int $start Day of year of start day
     * @param int $end Day of year of end day
     */
    public function __construct(public $set, public $start, public $end) {}
}

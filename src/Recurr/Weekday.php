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

use Recurr\Exception\InvalidWeekday;

/**
 * Class Weekday is a storage container for a day of the week.
 *
 * @package Recurr
 * @author  Shaun Simmons <shaun@envysphere.com>
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

    /** @var int|null nth occurrence of the weekday */
    public ?int $num = null;

    protected array $days = ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'];

    /**
     * @param int|string $weekday 0-6 or MO..SU
     * @param null|int   $num
     *
     * @throws InvalidWeekday
     */
    public function __construct(int|string $weekday, ?int $num)
    {
        if (is_numeric($weekday) && $weekday > 6 || $weekday < 0) {
            throw new InvalidWeekday('Day is not a valid weekday (0-6)');
        } elseif (!is_numeric($weekday) && !in_array($weekday, $this->days)) {
            throw new InvalidWeekday('Day is not a valid weekday (SU, MO, ...)');
        }

        if (!is_numeric($weekday)) {
            $weekday = array_search($weekday, $this->days);
        }

        $this->weekday = $weekday;
        $this->num = $num;
    }

    public function __toString(): string
    {
        return $this->num . $this->days[$this->weekday];
    }
}

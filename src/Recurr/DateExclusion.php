<?php

/*
 * Copyright 2014 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr;

/**
 * Class DateExclusion is a container for a single \DateTime.
 * The purpose of this class is to hold a flag that specifies whether
 * or not the \DateTime was created from a DATE only, or with a DATETIME.
 *
 * @package Recurr
 * @author  Shaun Simmons <shaun@envysphere.com>
 */
class DateExclusion
{
    /** @var \DateTime */
    public $date;

    /** @var bool Day of year */
    public $hasTime;

    /**
     * Constructor
     *
     * @param \DateTime $date
     * @param bool      $hasTime
     */
    public function __construct(\DateTime $date, $hasTime = true)
    {
        $this->date    = $date;
        $this->hasTime = $hasTime;
    }
}

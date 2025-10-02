<?php

/*
 * Copyright 2014 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr;

/**
 * Class DateExclusion is a container for a single \DateTimeInterface.
 *
 * The purpose of this class is to hold a flag that specifies whether
 * or not the \DateTimeInterface was created from a DATE only, or with a
 * DATETIME.
 *
 * It also tracks whether or not the exclusion is explicitly set to UTC.
 *
 * @author  Shaun Simmons <gh@simshaun.com>
 */
class DateExclusion
{
    /** @var \DateTimeInterface */
    public $date;

    /**
     * Constructor
     *
     * @param bool $hasTime
     * @param bool $isUtcExplicit
     */
    public function __construct(\DateTimeInterface $date, public $hasTime = true, public $isUtcExplicit = false)
    {
        $this->date = $date;
    }
}

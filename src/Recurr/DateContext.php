<?php

/*
 * Copyright 2025 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr;

/**
 * Base class for RDATE/EXDATE entries that wraps a DateTime with formatting context.
 *
 * Stores whether the original date string included time and UTC notation to preserve format when converting back to
 * RRULE string representation.
 *
 * @author Shaun Simmons <gh@simshaun.com>
 */
abstract class DateContext
{
    /**
     * @param \DateTime|\DateTimeImmutable $date The date/datetime to include or exclude
     * @param bool $hasTime Whether the original date string included a time component (default: true)
     * @param bool $isUtcExplicit Whether the original date string had explicit 'Z' UTC indicator
     */
    public function __construct(
        public \DateTime|\DateTimeImmutable $date,
        public bool $hasTime = true,
        public bool $isUtcExplicit = false,
    ) {}
}

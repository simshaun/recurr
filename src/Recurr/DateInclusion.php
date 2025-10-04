<?php

/*
 * Copyright 2015 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr;

/**
 * Represents a date to explicitly include in the recurrence set (RDATE in RFC 5545).
 *
 * These dates will be added to the generated occurrences regardless of whether they match the RRULE pattern or fall
 * outside the COUNT/UNTIL limits.
 *
 * @author Shaun Simmons <gh@simshaun.com>
 */
class DateInclusion extends DateContext {}

<?php

/*
 * Copyright 2025 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr;

/**
 * Represents a date to exclude from the recurrence set (EXDATE in RFC 5545).
 *
 * Dates matching this exclusion will be removed from the generated occurrences, even if they would normally be
 * included by the RRULE.
 *
 * @author Shaun Simmons <gh@simshaun.com>
 */
class DateExclusion extends DateContext {}

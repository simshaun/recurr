<?php

/*
 * Copyright 2025 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Collection of Recurrence objects with chainable date filtering methods.
 *
 * Extends Doctrine's ArrayCollection to provide convenient methods for filtering
 * recurrences by their start and end dates. All filter methods return a new
 * RecurrenceCollection, allowing method chaining.
 *
 * @author Shaun Simmons <gh@simshaun.com>
 *
 * @extends ArrayCollection<int, Recurrence>
 */
class RecurrenceCollection extends ArrayCollection
{
    /**
     * Filter recurrences with start dates between two dates.
     *
     * @param \DateTime|\DateTimeImmutable $after Start of date range
     * @param \DateTime|\DateTimeImmutable $before End of date range
     * @param bool $inc Include recurrences that start exactly on $after or $before (default: false)
     */
    public function startsBetween(
        \DateTime|\DateTimeImmutable $after,
        \DateTime|\DateTimeImmutable $before,
        bool $inc = false,
    ): RecurrenceCollection {
        return $this->filter(
            function (Recurrence $recurrence) use ($after, $before, $inc): bool {
                $start = $recurrence->getStart();

                if ($inc) {
                    return $start >= $after && $start <= $before;
                }

                return $start > $after && $start < $before;
            }
        );
    }

    /**
     * Filter recurrences with start dates before a specific date.
     *
     * @param \DateTime|\DateTimeImmutable $before Cutoff date
     * @param bool $inc Include recurrences that start exactly on $before (default: false)
     */
    public function startsBefore(\DateTime|\DateTimeImmutable $before, bool $inc = false): RecurrenceCollection
    {
        return $this->filter(
            function (Recurrence $recurrence) use ($before, $inc): bool {
                $start = $recurrence->getStart();

                if ($inc) {
                    return $start <= $before;
                }

                return $start < $before;
            }
        );
    }

    /**
     * Filter recurrences with start dates after a specific date.
     *
     * @param \DateTime|\DateTimeImmutable $after Cutoff date
     * @param bool $inc Include recurrences that start exactly on $after (default: false)
     */
    public function startsAfter(\DateTime|\DateTimeImmutable $after, bool $inc = false): RecurrenceCollection
    {
        return $this->filter(
            function (Recurrence $recurrence) use ($after, $inc): bool {
                $start = $recurrence->getStart();

                if ($inc) {
                    return $start >= $after;
                }

                return $start > $after;
            }
        );
    }

    /**
     * Filter recurrences with end dates between two dates.
     *
     * @param \DateTime|\DateTimeImmutable $after Start of date range
     * @param \DateTime|\DateTimeImmutable $before End of date range
     * @param bool $inc Include recurrences that end exactly on $after or $before (default: false)
     */
    public function endsBetween(
        \DateTime|\DateTimeImmutable $after,
        \DateTime|\DateTimeImmutable $before,
        bool $inc = false,
    ): RecurrenceCollection {
        return $this->filter(
            function (Recurrence $recurrence) use ($after, $before, $inc): bool {
                $end = $recurrence->getEnd();

                if ($inc) {
                    return $end >= $after && $end <= $before;
                }

                return $end > $after && $end < $before;
            }
        );
    }

    /**
     * Filter recurrences with end dates before a specific date.
     *
     * @param \DateTime|\DateTimeImmutable $before Cutoff date
     * @param bool $inc Include recurrences that end exactly on $before (default: false)
     */
    public function endsBefore(\DateTime|\DateTimeImmutable $before, bool $inc = false): RecurrenceCollection
    {
        return $this->filter(
            function (Recurrence $recurrence) use ($before, $inc): bool {
                $end = $recurrence->getEnd();

                if ($inc) {
                    return $end <= $before;
                }

                return $end < $before;
            }
        );
    }

    /**
     * Filter recurrences with end dates after a specific date.
     *
     * @param \DateTime|\DateTimeImmutable $after Cutoff date
     * @param bool $inc Include recurrences that end exactly on $after (default: false)
     */
    public function endsAfter(\DateTime|\DateTimeImmutable $after, bool $inc = false): RecurrenceCollection
    {
        return $this->filter(
            function (Recurrence $recurrence) use ($after, $inc): bool {
                $end = $recurrence->getEnd();

                if ($inc) {
                    return $end >= $after;
                }

                return $end > $after;
            }
        );
    }
}

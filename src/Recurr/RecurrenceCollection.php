<?php

/*
 * Copyright 2014 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr;

use Doctrine\Common\Collections\ArrayCollection as BaseCollection;

/**
 * @author  Shaun Simmons <gh@simshaun.com>
 *
 * @template T
 */
class RecurrenceCollection extends BaseCollection
{
    /**
     * @param bool $inc include $after or $before if they happen to be a recurrence
     *
     * @return RecurrenceCollection<T>
     */
    public function startsBetween(\DateTimeInterface $after, \DateTimeInterface $before, $inc = false)
    {
        return $this->filter(
            function ($recurrence) use ($after, $before, $inc): bool {
                /** @var Recurrence $recurrence */
                $start = $recurrence->getStart();

                if ($inc) {
                    return $start >= $after && $start <= $before;
                }

                return $start > $after && $start < $before;
            }
        );
    }

    /**
     * @param bool $inc include $before if it is a recurrence
     *
     * @return RecurrenceCollection<T>
     */
    public function startsBefore(\DateTimeInterface $before, $inc = false)
    {
        return $this->filter(
            function ($recurrence) use ($before, $inc): bool {
                /** @var Recurrence $recurrence */
                $start = $recurrence->getStart();

                if ($inc) {
                    return $start <= $before;
                }

                return $start < $before;
            }
        );
    }

    /**
     * @param bool $inc include $after if it a recurrence
     *
     * @return RecurrenceCollection<T>
     */
    public function startsAfter(\DateTimeInterface $after, $inc = false)
    {
        return $this->filter(
            function ($recurrence) use ($after, $inc): bool {
                /** @var Recurrence $recurrence */
                $start = $recurrence->getStart();

                if ($inc) {
                    return $start >= $after;
                }

                return $start > $after;
            }
        );
    }

    /**
     * @param bool $inc include $after or $before if they happen to be a recurrence
     *
     * @return RecurrenceCollection<T>
     */
    public function endsBetween(\DateTimeInterface $after, \DateTimeInterface $before, $inc = false)
    {
        return $this->filter(
            function ($recurrence) use ($after, $before, $inc): bool {
                /** @var Recurrence $recurrence */
                $end = $recurrence->getEnd();

                if ($inc) {
                    return $end >= $after && $end <= $before;
                }

                return $end > $after && $end < $before;
            }
        );
    }

    /**
     * @param bool $inc include $before if it is a recurrence
     *
     * @return RecurrenceCollection<T>
     */
    public function endsBefore(\DateTimeInterface $before, $inc = false)
    {
        return $this->filter(
            function ($recurrence) use ($before, $inc): bool {
                /** @var Recurrence $recurrence */
                $end = $recurrence->getEnd();

                if ($inc) {
                    return $end <= $before;
                }

                return $end < $before;
            }
        );
    }

    /**
     * @param bool $inc include $after if it a recurrence
     *
     * @return RecurrenceCollection<T>
     */
    public function endsAfter(\DateTimeInterface $after, $inc = false)
    {
        return $this->filter(
            function ($recurrence) use ($after, $inc): bool {
                /** @var Recurrence $recurrence */
                $end = $recurrence->getEnd();

                if ($inc) {
                    return $end >= $after;
                }

                return $end > $after;
            }
        );
    }
}

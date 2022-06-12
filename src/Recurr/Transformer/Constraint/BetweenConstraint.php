<?php

/*
 * Copyright 2014 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr\Transformer\Constraint;

use DateTimeInterface;
use Recurr\Transformer\Constraint;

class BetweenConstraint extends Constraint
{
    protected bool $stopsTransformer = false;

    protected DateTimeInterface $before;

    protected DateTimeInterface $after;

    protected bool $inc;

    /**
     * @param DateTimeInterface $after
     * @param DateTimeInterface $before
     * @param bool              $inc Include date if it equals $after or $before.
     */
    public function __construct(DateTimeInterface $after, DateTimeInterface $before, bool $inc = false)
    {
        $this->after  = $after;
        $this->before = $before;
        $this->inc    = $inc;
    }

    /**
     * Passes if $date is between $after and $before
     */
    public function test(DateTimeInterface $date): bool
    {
        if ($date > $this->before) {
            $this->stopsTransformer = true;
        }

        if ($this->inc) {
            return $date >= $this->after && $date <= $this->before;
        }

        return $date > $this->after && $date < $this->before;
    }

    public function getBefore(): DateTimeInterface
    {
        return $this->before;
    }

    public function getAfter(): DateTimeInterface
    {
        return $this->after;
    }

    public function isInc(): bool
    {
        return $this->inc;
    }
}

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

class AfterConstraint extends Constraint
{
    protected bool $stopsTransformer = false;

    protected DateTimeInterface $after;

    protected bool $inc;

    /**
     * @param DateTimeInterface $after
     * @param bool               $inc Include date if it equals $after.
     */
    public function __construct(DateTimeInterface $after, bool $inc = false)
    {
        $this->after = $after;
        $this->inc    = $inc;
    }

    /**
     * Passes if $date is after $after
     */
    public function test(DateTimeInterface $date): bool
    {
        if ($this->inc) {
            return $date >= $this->after;
        }

        return $date > $this->after;
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

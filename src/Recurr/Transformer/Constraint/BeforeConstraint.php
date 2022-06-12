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

class BeforeConstraint extends Constraint
{
    protected bool $stopsTransformer = true;

    protected DateTimeInterface $before;

    protected bool $inc;

    /**
     * @param DateTimeInterface $before
     * @param bool               $inc Include date if it equals $before.
     */
    public function __construct(DateTimeInterface $before, bool $inc = false)
    {
        $this->before = $before;
        $this->inc    = $inc;
    }

    /**
     * Passes if $date is before $before
     */
    public function test(DateTimeInterface $date): bool
    {
        if ($this->inc) {
            return $date <= $this->before;
        }

        return $date < $this->before;
    }

    public function getBefore(): DateTimeInterface
    {
        return $this->before;
    }

    public function isInc(): bool
    {
        return $this->inc;
    }
}

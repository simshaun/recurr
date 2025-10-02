<?php

/*
 * Copyright 2014 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr\Transformer\Constraint;

use Recurr\Transformer\Constraint;

class BetweenConstraint extends Constraint
{
    protected $stopsTransformer = false;

    /**
     * @param bool $inc include date if it equals $after or $before
     */
    public function __construct(protected \DateTimeInterface $after, protected \DateTimeInterface $before, protected $inc = false) {}

    /**
     * Passes if $date is between $after and $before
     *
     * {@inheritdoc}
     */
    public function test(\DateTimeInterface $date): bool
    {
        if ($date > $this->before) {
            $this->stopsTransformer = true;
        }

        if ($this->inc) {
            return $date >= $this->after && $date <= $this->before;
        }

        return $date > $this->after && $date < $this->before;
    }

    public function getBefore(): \DateTimeInterface
    {
        return $this->before;
    }

    public function getAfter(): \DateTimeInterface
    {
        return $this->after;
    }

    /**
     * @return bool
     */
    public function isInc()
    {
        return $this->inc;
    }
}

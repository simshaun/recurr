<?php

/*
 * Copyright 2014 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr\Transformer\Constraint;

use Recurr\Transformer\Constraint;

class AfterConstraint extends Constraint
{
    protected $stopsTransformer = false;

    /**
     * @param bool $inc include date if it equals $after
     */
    public function __construct(protected \DateTimeInterface $after, protected $inc = false) {}

    /**
     * Passes if $date is after $after
     *
     * {@inheritdoc}
     */
    public function test(\DateTimeInterface $date): bool
    {
        if ($this->inc) {
            return $date >= $this->after;
        }

        return $date > $this->after;
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

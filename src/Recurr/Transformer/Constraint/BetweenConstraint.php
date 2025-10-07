<?php

/*
 * Copyright 2025 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr\Transformer\Constraint;

use Recurr\Transformer\Constraint;

class BetweenConstraint extends Constraint
{
    protected bool $stopsTransformer = false;

    /**
     * @param bool $inc If comparison should be inclusive. (Include date if it equals $before or $after)
     */
    public function __construct(
        protected \DateTimeInterface $after,
        protected \DateTimeInterface $before,
        protected bool $inc = false,
    ) {}

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
     * @deprecated Since v6. Use isInclusive()
     */
    public function isInc(): bool
    {
        return $this->isInclusive();
    }

    public function isInclusive(): bool
    {
        return $this->inc;
    }
}

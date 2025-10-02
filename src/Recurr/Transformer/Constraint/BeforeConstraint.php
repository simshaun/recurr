<?php

/*
 * Copyright 2014 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr\Transformer\Constraint;

use Recurr\Transformer\Constraint;

class BeforeConstraint extends Constraint
{
    protected $stopsTransformer = true;

    /**
     * @param bool $inc include date if it equals $before
     */
    public function __construct(protected \DateTimeInterface $before, protected $inc = false) {}

    /**
     * Passes if $date is before $before
     *
     * {@inheritdoc}
     */
    public function test(\DateTimeInterface $date): bool
    {
        if ($this->inc) {
            return $date <= $this->before;
        }

        return $date < $this->before;
    }

    public function getBefore(): \DateTimeInterface
    {
        return $this->before;
    }

    /**
     * @return bool
     */
    public function isInc()
    {
        return $this->inc;
    }
}

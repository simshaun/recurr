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

    /** @var \DateTime */
    protected $before;

    /** @var \DateTime */
    protected $after;

    /** @var bool */
    protected $inc;

    /**
     * @param \DateTime $after
     * @param \DateTime $before
     * @param bool      $inc Include date if it equals $after or $before.
     */
    public function __construct(\DateTime $after, \DateTime $before, $inc = false)
    {
        $this->after  = $after;
        $this->before = $before;
        $this->inc    = $inc;
    }

    /**
     * Passes if $date is between $after and $before
     *
     * {@inheritdoc}
     */
    public function test(\DateTime $date)
    {
        if ($date > $this->before) {
            $this->stopsTransformer = true;
        }

        if ($this->inc) {
            return $date >= $this->after && $date <= $this->before;
        }

        return $date > $this->after && $date < $this->before;
    }
}
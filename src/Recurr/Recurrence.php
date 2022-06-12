<?php

/*
 * Copyright 2014 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr;

use DateTimeInterface;

/**
 * Class Recurrence is responsible for storing the start and end \DateTime of
 * a specific recurrence in a RRULE.
 *
 * @package Recurr
 * @author  Shaun Simmons <shaun@envysphere.com>
 */
class Recurrence
{
    protected ?DateTimeInterface $start = null;

    protected ?DateTimeInterface $end = null;

    protected int $index;

    public function __construct(?DateTimeInterface $start = null, ?DateTimeInterface $end = null, $index = 0)
    {
        if ($start instanceof DateTimeInterface) {
            $this->setStart($start);
        }

        if ($end instanceof DateTimeInterface) {
            $this->setEnd($end);
        }

        $this->index = $index;
    }

    public function getStart(): ?DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(?DateTimeInterface $start)
    {
        $this->start = $start;
    }

    public function getEnd(): ?DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(?DateTimeInterface $end): void
    {
        $this->end = $end;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function setIndex(int $index): void
    {
        $this->index = $index;
    }
}

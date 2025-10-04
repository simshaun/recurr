<?php

/*
 * Copyright 2025 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr;

/**
 * @author Shaun Simmons <gh@simshaun.com>
 */
class Recurrence
{
    protected \DateTime|\DateTimeImmutable $start;

    protected \DateTime|\DateTimeImmutable $end;

    public function __construct(
        \DateTime|\DateTimeImmutable|null $start = null,
        \DateTime|\DateTimeImmutable|null $end = null,
        protected int $index = 0,
    ) {
        if ($start) {
            $this->setStart($start);
        }

        if ($end) {
            $this->setEnd($end);
        }
    }

    public function getStart(): \DateTime|\DateTimeImmutable
    {
        return $this->start;
    }

    public function setStart(\DateTime|\DateTimeImmutable $start): void
    {
        $this->start = $start;
    }

    public function getEnd(): \DateTime|\DateTimeImmutable
    {
        return $this->end;
    }

    public function setEnd(\DateTime|\DateTimeImmutable $end): void
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

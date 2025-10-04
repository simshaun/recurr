<?php

/*
 * Copyright 2025 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr\Transformer;

class ArrayTransformerConfig
{
    protected int $virtualLimit = 732;

    protected bool $lastDayOfMonthFix = false;

    /**
     * Declare the virtual limit imposed upon infinitely recurring events.
     */
    public function setVirtualLimit(int $virtualLimit): static
    {
        $this->virtualLimit = $virtualLimit;

        return $this;
    }

    /**
     * Get the virtual limit imposed upon infinitely recurring events.
     */
    public function getVirtualLimit(): int
    {
        return $this->virtualLimit;
    }

    /**
     * By default, January 30 + 1 month goes to March 30 because February doesn't have 30 days.
     *
     * Enabling this fix tells Recurr that +1 month means "last day of next month".
     */
    public function enableLastDayOfMonthFix(): void
    {
        $this->lastDayOfMonthFix = true;
    }

    public function disableLastDayOfMonthFix(): void
    {
        $this->lastDayOfMonthFix = false;
    }

    public function isLastDayOfMonthFixEnabled(): bool
    {
        return $this->lastDayOfMonthFix;
    }
}

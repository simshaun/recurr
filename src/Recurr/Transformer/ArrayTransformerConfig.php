<?php

/*
 * Copyright 2014 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr\Transformer;

class ArrayTransformerConfig
{
    /** @var int */
    protected $virtualLimit = 732;

    protected $lastDayOfMonthFix = false;

    /** @var string */
    protected $recurrenceClassName = '\Recurr\Recurrence';

    /**
     * Set the virtual limit imposed upon infinitely recurring events.
     *
     * @param int $virtualLimit The limit
     *
     * @return $this
     */
    public function setVirtualLimit($virtualLimit)
    {
        $this->virtualLimit = (int) $virtualLimit;

        return $this;
    }

    /**
     * Get the virtual limit imposed upon infinitely recurring events.
     *
     * @return int
     */
    public function getVirtualLimit()
    {
        return $this->virtualLimit;
    }

    /**
     * By default, January 30 + 1 month results in March 30 because February doesn't have 30 days.
     *
     * Enabling this fix tells Recurr that +1 month means "last day of next month".
     */
    public function enableLastDayOfMonthFix()
    {
        $this->lastDayOfMonthFix = true;
    }

    public function disableLastDayOfMonthFix()
    {
        $this->lastDayOfMonthFix = false;
    }

    /**
     * @return boolean
     */
    public function isLastDayOfMonthFixEnabled()
    {
        return $this->lastDayOfMonthFix;
    }

    /**
     * @return string
     */
    public function getRecurrenceClassName()
    {
        return $this->recurrenceClassName;
    }

    /**
     * Set the class to use when generating a collection of recurrences
     * Defaults to the Recurrence class in this package
     *
     * @param string $recurrenceClassName
     */
    public function setRecurrenceClassName($recurrenceClassName)
    {
        $this->recurrenceClassName = $recurrenceClassName;
    }
}

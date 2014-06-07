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
    protected $lastDayOfMonthFix = false;

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
}
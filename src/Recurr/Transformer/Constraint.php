<?php

/*
 * Copyright 2025 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr\Transformer;

abstract class Constraint implements ConstraintInterface
{
    protected bool $stopsTransformer = true;

    public function stopsTransformer(): bool
    {
        return $this->stopsTransformer;
    }
}

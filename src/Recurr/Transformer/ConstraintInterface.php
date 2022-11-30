<?php

/*
 * Copyright 2014 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Recurr\Transformer;

use DateTimeInterface;

interface ConstraintInterface
{
    public function stopsTransformer(): bool;

    public function test(DateTimeInterface $date): bool;
}

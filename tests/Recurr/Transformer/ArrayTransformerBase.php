<?php

namespace Tests\Recurr\Transformer;

use PHPUnit\Framework\TestCase;
use Recurr\Transformer\ArrayTransformer;

class ArrayTransformerBase extends TestCase
{
    protected ArrayTransformer $transformer;

    protected string $timezone = 'America/New_York';

    public function setUp(): void
    {
        $this->transformer = new ArrayTransformer();
    }
}

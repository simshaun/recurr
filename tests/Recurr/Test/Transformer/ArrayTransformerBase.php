<?php

namespace Recurr\Test\Transformer;

use PHPUnit\Framework\TestCase;
use Recurr\Transformer\ArrayTransformer;

class ArrayTransformerBase extends TestCase
{
    protected ArrayTransformer $transformer;

    protected string $timezone = 'America/New_York';

    protected array $defaults = ['FREQ' => 'DAILY'];

    public function setUp(): void
    {
        $this->transformer = new ArrayTransformer();
    }
}

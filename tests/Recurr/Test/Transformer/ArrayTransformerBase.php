<?php

namespace Recurr\Test\Transformer;

use PHPUnit\Framework\TestCase;
use Recurr\Transformer\ArrayTransformer;

class ArrayTransformerBase extends TestCase
{
    /** @var ArrayTransformer */
    protected $transformer;

    protected $timezone = 'America/New_York';

    public function setUp(): void
    {
        $this->transformer = new ArrayTransformer();
    }
}

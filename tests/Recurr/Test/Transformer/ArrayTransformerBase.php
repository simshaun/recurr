<?php

namespace Recurr\Test\Transformer;

use Recurr\Transformer\ArrayTransformer;

class ArrayTransformerBase extends \PHPUnit\Framework\TestCase
{
    /** @var ArrayTransformer */
    protected $transformer;

    protected $timezone = 'America/New_York';

    public function setUp(): void
    {
        $this->transformer = new ArrayTransformer();
    }
}

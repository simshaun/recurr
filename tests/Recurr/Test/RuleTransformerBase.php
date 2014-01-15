<?php

namespace Recurr\Test;

use Recurr\RuleTransformer;

class RuleTransformerBase extends \PHPUnit_Framework_TestCase
{
    /** @var RuleTransformer */
    protected $transformer;

    protected $timezone = 'America/New_York';

    public function setUp()
    {
        $this->transformer = new RuleTransformer;
    }
}

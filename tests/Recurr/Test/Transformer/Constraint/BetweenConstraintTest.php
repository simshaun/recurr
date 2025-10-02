<?php

namespace Recurr\Test\Transformer\Filter;

use PHPUnit\Framework\TestCase;
use Recurr\Transformer\Constraint\BetweenConstraint;

class BetweenConstraintTest extends TestCase
{
    public function testBetween(): void
    {
        $after = new \DateTime('2014-06-10');
        $before = new \DateTime('2014-06-17');

        $constraint = new BetweenConstraint($after, $before, false);
        $testResult = $constraint->test(new \DateTime('2014-06-17'));

        $this->assertFalse($testResult);
    }

    public function testBetweenInc(): void
    {
        $after = new \DateTime('2014-06-10');
        $before = new \DateTime('2014-06-17');

        $constraint = new BetweenConstraint($after, $before, true);
        $testResult = $constraint->test(new \DateTime('2014-06-17'));

        $this->assertTrue($testResult);
    }
}

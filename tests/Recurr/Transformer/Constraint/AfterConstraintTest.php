<?php

namespace Tests\Recurr\Transformer\Constraint;

use PHPUnit\Framework\TestCase;
use Recurr\Transformer\Constraint\AfterConstraint;

class AfterConstraintTest extends TestCase
{
    public function testAfter(): void
    {
        $after = new \DateTime('2014-06-17');

        $constraint = new AfterConstraint($after, false);
        $testResult = $constraint->test(new \DateTime('2014-06-17'));

        $this->assertFalse($testResult);
    }

    public function testAfterInc(): void
    {
        $after = new \DateTime('2014-06-17');

        $constraint = new AfterConstraint($after, true);
        $testResult = $constraint->test(new \DateTime('2014-06-17'));

        $this->assertTrue($testResult);
    }
}

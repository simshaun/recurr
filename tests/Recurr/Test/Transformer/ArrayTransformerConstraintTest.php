<?php

namespace Recurr\Test\Transformer;

use Recurr\Rule;
use Recurr\Transformer\Constraint\AfterConstraint;
use Recurr\Transformer\Constraint\BeforeConstraint;
use Recurr\Transformer\Constraint\BetweenConstraint;

class ArrayTransformerConstraintTest extends ArrayTransformerBase
{
    public function testBefore()
    {
        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=5', new \DateTime('2014-03-16 04:00:00')
        );

        $constraint = new BeforeConstraint(new \DateTime('2014-05-16 04:00:00'), false);
        $computed   = $this->transformer->transform($rule, null, $constraint);
        $this->assertCount(2, $this->transformer->transform($rule, null, $constraint));
        $this->assertEquals(new \DateTime('2014-03-16 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2014-04-16 04:00:00'), $computed[1]->getStart());

        $constraint = new BeforeConstraint(new \DateTime('2014-05-16 04:00:00'), true);
        $computed   = $this->transformer->transform($rule, null, $constraint);
        $this->assertCount(3, $this->transformer->transform($rule, null, $constraint));
        $this->assertEquals(new \DateTime('2014-03-16 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2014-04-16 04:00:00'), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2014-05-16 04:00:00'), $computed[2]->getStart());
    }

    public function testAfter()
    {
        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=5', new \DateTime('2014-03-16 04:00:00')
        );

        $constraint = new AfterConstraint(new \DateTime('2014-05-16 04:00:00'), false);
        $computed   = $this->transformer->transform($rule, null, $constraint);
        $this->assertCount(5, $computed);
        $this->assertEquals(new \DateTime('2014-06-16 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2014-07-16 04:00:00'), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2014-08-16 04:00:00'), $computed[2]->getStart());
        $this->assertEquals(new \DateTime('2014-09-16 04:00:00'), $computed[3]->getStart());
        $this->assertEquals(new \DateTime('2014-10-16 04:00:00'), $computed[4]->getStart());

        $constraint = new AfterConstraint(new \DateTime('2014-05-16 04:00:00'), true);
        $computed   = $this->transformer->transform($rule, null, $constraint);
        $this->assertCount(5, $computed);
        $this->assertEquals(new \DateTime('2014-05-16 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2014-06-16 04:00:00'), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2014-07-16 04:00:00'), $computed[2]->getStart());
        $this->assertEquals(new \DateTime('2014-08-16 04:00:00'), $computed[3]->getStart());
        $this->assertEquals(new \DateTime('2014-09-16 04:00:00'), $computed[4]->getStart());
    }

    public function testAfterFarFuture()
    {
        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=5', new \DateTime('2014-03-16 04:00:00')
        );

        $constraint = new AfterConstraint(new \DateTime('2020-05-16 04:00:00'), false);
        $computed   = $this->transformer->transform($rule, null, $constraint);
        $this->assertCount(5, $computed);
        $this->assertEquals(new \DateTime('2020-06-16 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2020-07-16 04:00:00'), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2020-08-16 04:00:00'), $computed[2]->getStart());
        $this->assertEquals(new \DateTime('2020-09-16 04:00:00'), $computed[3]->getStart());
        $this->assertEquals(new \DateTime('2020-10-16 04:00:00'), $computed[4]->getStart());
    }

    public function testBetween()
    {
        $rule = new Rule(
            'FREQ=MONTHLY', new \DateTime('2014-03-16 04:00:00')
        );

        $constraint = new BetweenConstraint(
            new \DateTime('2014-03-16 04:00:00'), new \DateTime('2014-07-16 04:00:00'), false
        );
        $computed   = $this->transformer->transform($rule, null, $constraint);
        $this->assertCount(3, $computed);
        $this->assertEquals(new \DateTime('2014-04-16 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2014-05-16 04:00:00'), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2014-06-16 04:00:00'), $computed[2]->getStart());

        $constraint = new BetweenConstraint(
            new \DateTime('2014-03-16 04:00:00'), new \DateTime('2014-07-16 04:00:00'), true
        );
        $computed   = $this->transformer->transform($rule, null, $constraint);
        $this->assertCount(5, $computed);
        $this->assertEquals(new \DateTime('2014-03-16 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2014-04-16 04:00:00'), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2014-05-16 04:00:00'), $computed[2]->getStart());
        $this->assertEquals(new \DateTime('2014-06-16 04:00:00'), $computed[3]->getStart());
        $this->assertEquals(new \DateTime('2014-07-16 04:00:00'), $computed[4]->getStart());
    }
}

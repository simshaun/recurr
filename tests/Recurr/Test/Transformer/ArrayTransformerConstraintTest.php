<?php

namespace Recurr\Test\Transformer;

use Recurr\Rule;
use Recurr\Transformer\Constraint\AfterConstraint;
use Recurr\Transformer\Constraint\BeforeConstraint;
use Recurr\Transformer\Constraint\BetweenConstraint;

class ArrayTransformerConstraintTest extends ArrayTransformerBase
{
    /**
     * @param string $dateTimeClassName \DateTimeImmutable or \DateTime
     */
    private function _testBefore($dateTimeClassName)
    {
        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=5', new $dateTimeClassName('2014-03-16 04:00:00')
        );

        $constraint = new BeforeConstraint(new $dateTimeClassName('2014-05-16 04:00:00'), false);
        $computed   = $this->transformer->transform($rule, $constraint);
        $this->assertCount(2, $computed);
        $this->assertEquals(new $dateTimeClassName('2014-03-16 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-04-16 04:00:00'), $computed[1]->getStart());

        $constraint = new BeforeConstraint(new $dateTimeClassName('2014-05-16 04:00:00'), true);
        $computed   = $this->transformer->transform($rule, $constraint);
        $this->assertCount(3, $computed);
        $this->assertEquals(new $dateTimeClassName('2014-03-16 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-04-16 04:00:00'), $computed[1]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-05-16 04:00:00'), $computed[2]->getStart());
    }

    public function testBeforeMutable()
    {
        $this->_testBefore('\DateTime');
    }

    public function testBeforeImmutable()
    {
        $this->_testBefore('\DateTimeImmutable');
    }

    /**
     * @param string $dateTimeClassName \DateTimeImmutable or \DateTime
     */
    private function _testAfter($dateTimeClassName)
    {
        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=5', new $dateTimeClassName('2014-03-16 04:00:00')
        );

        $constraint = new AfterConstraint(new $dateTimeClassName('2014-05-16 04:00:00'), false);

        // Count instances that fail the constraint towards the rule's limit.
        $computed = $this->transformer->transform($rule, $constraint);
        $this->assertCount(2, $computed);
        $this->assertEquals(new $dateTimeClassName('2014-06-16 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-07-16 04:00:00'), $computed[1]->getStart());

        // Do not count instances that fail the constraint towards the rule's limit.
        $computed = $this->transformer->transform($rule, $constraint, false);
        $this->assertCount(5, $computed);
        $this->assertEquals(new $dateTimeClassName('2014-06-16 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-07-16 04:00:00'), $computed[1]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-08-16 04:00:00'), $computed[2]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-09-16 04:00:00'), $computed[3]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-10-16 04:00:00'), $computed[4]->getStart());

        $constraint = new AfterConstraint(new $dateTimeClassName('2014-05-16 04:00:00'), true);

        // Count instances that fail the constraint towards the rule's limit.
        $computed = $this->transformer->transform($rule, $constraint);
        $this->assertCount(3, $computed);
        $this->assertEquals(new $dateTimeClassName('2014-05-16 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-06-16 04:00:00'), $computed[1]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-07-16 04:00:00'), $computed[2]->getStart());

        // Do not count instances that fail the constraint towards the rule's limit.
        $computed = $this->transformer->transform($rule, $constraint, false);
        $this->assertCount(5, $computed);
        $this->assertEquals(new $dateTimeClassName('2014-05-16 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-06-16 04:00:00'), $computed[1]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-07-16 04:00:00'), $computed[2]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-08-16 04:00:00'), $computed[3]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-09-16 04:00:00'), $computed[4]->getStart());
    }

    public function testAfterMutable()
    {
        $this->_testAfter('\DateTime');
    }

    public function testAfterImmutable()
    {
        $this->_testAfter('\DateTimeImmutable');
    }

    /**
     * @param string $dateTimeClassName \DateTimeImmutable or \DateTime
     */
    private function _testAfterFarFuture($dateTimeClassName)
    {
        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=5', new $dateTimeClassName('2014-03-16 04:00:00')
        );

        $constraint = new AfterConstraint(new $dateTimeClassName('2020-05-16 04:00:00'), false);
        $computed   = $this->transformer->transform($rule, $constraint, false);
        $this->assertCount(5, $computed);
        $this->assertEquals(new $dateTimeClassName('2020-06-16 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new $dateTimeClassName('2020-07-16 04:00:00'), $computed[1]->getStart());
        $this->assertEquals(new $dateTimeClassName('2020-08-16 04:00:00'), $computed[2]->getStart());
        $this->assertEquals(new $dateTimeClassName('2020-09-16 04:00:00'), $computed[3]->getStart());
        $this->assertEquals(new $dateTimeClassName('2020-10-16 04:00:00'), $computed[4]->getStart());
    }

    public function testAfterFarFutureMutable()
    {
        $this->_testAfterFarFuture('\DateTime');
    }

    public function testAfterFarFutureImmutable()
    {
        $this->_testAfterFarFuture('\DateTimeImmutable');
    }

    /**
     * @param string $dateTimeClassName \DateTimeImmutable or \DateTime
     */
    private function _testBetween($dateTimeClassName)
    {
        $rule = new Rule(
            'FREQ=MONTHLY', new $dateTimeClassName('2014-03-16 04:00:00')
        );

        $constraint = new BetweenConstraint(
            new $dateTimeClassName('2014-03-16 04:00:00'), new $dateTimeClassName('2014-07-16 04:00:00'), false
        );
        $computed   = $this->transformer->transform($rule, $constraint);
        $this->assertCount(3, $computed);
        $this->assertEquals(new $dateTimeClassName('2014-04-16 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-05-16 04:00:00'), $computed[1]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-06-16 04:00:00'), $computed[2]->getStart());

        $constraint = new BetweenConstraint(
            new $dateTimeClassName('2014-03-16 04:00:00'), new $dateTimeClassName('2014-07-16 04:00:00'), true
        );
        $computed   = $this->transformer->transform($rule, $constraint);
        $this->assertCount(5, $computed);
        $this->assertEquals(new $dateTimeClassName('2014-03-16 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-04-16 04:00:00'), $computed[1]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-05-16 04:00:00'), $computed[2]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-06-16 04:00:00'), $computed[3]->getStart());
        $this->assertEquals(new $dateTimeClassName('2014-07-16 04:00:00'), $computed[4]->getStart());
    }

    public function testBetweenMutable()
    {
        $this->_testBetween('\DateTime');
    }

    public function testBetweenImmutable()
    {
        $this->_testBetween('\DateTimeImmutable');
    }
}

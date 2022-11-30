<?php

namespace Recurr\Test\Transformer;

use DateTime;
use Recurr\Rule;

class ArrayTransformerBySecondTest extends ArrayTransformerBase
{
    public function testBySecondSecondly(): void
    {
        $rule = new Rule(
            'FREQ=SECONDLY;COUNT=5;BYSECOND=36,45',
            new DateTime('2013-06-12 16:00:00')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new DateTime('2013-06-12 16:00:36'), $computed[0]->getStart());
        $this->assertEquals(new DateTime('2013-06-12 16:00:45'), $computed[1]->getStart());
        $this->assertEquals(new DateTime('2013-06-12 16:01:36'), $computed[2]->getStart());
        $this->assertEquals(new DateTime('2013-06-12 16:01:45'), $computed[3]->getStart());
        $this->assertEquals(new DateTime('2013-06-12 16:02:36'), $computed[4]->getStart());
    }

    public function testBySecondMinutely(): void
    {
        $rule = new Rule(
            'FREQ=MINUTELY;COUNT=5;BYSECOND=36,45',
            new DateTime('2013-06-12 16:00:00')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new DateTime('2013-06-12 16:00:36'), $computed[0]->getStart());
        $this->assertEquals(new DateTime('2013-06-12 16:00:45'), $computed[1]->getStart());
        $this->assertEquals(new DateTime('2013-06-12 16:01:36'), $computed[2]->getStart());
        $this->assertEquals(new DateTime('2013-06-12 16:01:45'), $computed[3]->getStart());
        $this->assertEquals(new DateTime('2013-06-12 16:02:36'), $computed[4]->getStart());
    }

    public function testBySecondHourly(): void
    {
        $rule = new Rule(
            'FREQ=HOURLY;COUNT=5;BYSECOND=36,45',
            new DateTime('2013-06-12 16:00:00')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new DateTime('2013-06-12 16:00:36'), $computed[0]->getStart());
        $this->assertEquals(new DateTime('2013-06-12 16:00:45'), $computed[1]->getStart());
        $this->assertEquals(new DateTime('2013-06-12 17:00:36'), $computed[2]->getStart());
        $this->assertEquals(new DateTime('2013-06-12 17:00:45'), $computed[3]->getStart());
        $this->assertEquals(new DateTime('2013-06-12 18:00:36'), $computed[4]->getStart());
    }

    public function testBySecondDaily(): void
    {
        $rule = new Rule(
            'FREQ=DAILY;COUNT=5;BYSECOND=36,45',
            new DateTime('2013-06-12 16:00:00')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new DateTime('2013-06-12 16:00:36'), $computed[0]->getStart());
        $this->assertEquals(new DateTime('2013-06-12 16:00:45'), $computed[1]->getStart());
        $this->assertEquals(new DateTime('2013-06-13 16:00:36'), $computed[2]->getStart());
        $this->assertEquals(new DateTime('2013-06-13 16:00:45'), $computed[3]->getStart());
        $this->assertEquals(new DateTime('2013-06-14 16:00:36'), $computed[4]->getStart());
    }

    public function testBySecondWeekly(): void
    {
        $rule = new Rule(
            'FREQ=WEEKLY;COUNT=5;BYSECOND=36,45',
            new DateTime('2013-06-12 16:00:00')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new DateTime('2013-06-12 16:00:36'), $computed[0]->getStart());
        $this->assertEquals(new DateTime('2013-06-12 16:00:45'), $computed[1]->getStart());
        $this->assertEquals(new DateTime('2013-06-19 16:00:36'), $computed[2]->getStart());
        $this->assertEquals(new DateTime('2013-06-19 16:00:45'), $computed[3]->getStart());
        $this->assertEquals(new DateTime('2013-06-26 16:00:36'), $computed[4]->getStart());
    }

    public function testBySecondMonthly(): void
    {
        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=5;BYSECOND=36,45',
            new DateTime('2013-06-12 16:00:00')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new DateTime('2013-06-12 16:00:36'), $computed[0]->getStart());
        $this->assertEquals(new DateTime('2013-06-12 16:00:45'), $computed[1]->getStart());
        $this->assertEquals(new DateTime('2013-07-12 16:00:36'), $computed[2]->getStart());
        $this->assertEquals(new DateTime('2013-07-12 16:00:45'), $computed[3]->getStart());
        $this->assertEquals(new DateTime('2013-08-12 16:00:36'), $computed[4]->getStart());
    }

    public function testBySecondYearly(): void
    {
        $rule = new Rule(
            'FREQ=YEARLY;COUNT=5;BYSECOND=36,45',
            new DateTime('2013-06-12 16:00:00')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new DateTime('2013-06-12 16:00:36'), $computed[0]->getStart());
        $this->assertEquals(new DateTime('2013-06-12 16:00:45'), $computed[1]->getStart());
        $this->assertEquals(new DateTime('2014-06-12 16:00:36'), $computed[2]->getStart());
        $this->assertEquals(new DateTime('2014-06-12 16:00:45'), $computed[3]->getStart());
        $this->assertEquals(new DateTime('2015-06-12 16:00:36'), $computed[4]->getStart());
    }
}

<?php

namespace Tests\Recurr\Transformer;

use Recurr\Rule;

class ArrayTransformerHourlyTest extends ArrayTransformerBase
{
    public function testHourly(): void
    {
        $rule = new Rule(
            'FREQ=HOURLY;COUNT=5;',
            new \DateTime('2013-02-28 23:00:00')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new \DateTime('2013-02-28 23:00:00'), $computed[0]?->getStart());
        $this->assertEquals(new \DateTime('2013-03-01 00:00:00'), $computed[1]?->getStart());
        $this->assertEquals(new \DateTime('2013-03-01 01:00:00'), $computed[2]?->getStart());
        $this->assertEquals(new \DateTime('2013-03-01 02:00:00'), $computed[3]?->getStart());
        $this->assertEquals(new \DateTime('2013-03-01 03:00:00'), $computed[4]?->getStart());
    }

    public function testHourlyInterval(): void
    {
        $rule = new Rule(
            'FREQ=HOURLY;COUNT=5;INTERVAL=9;',
            new \DateTime('2013-02-28 23:00:00')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new \DateTime('2013-02-28 23:00:00'), $computed[0]?->getStart());
        $this->assertEquals(new \DateTime('2013-03-01 08:00:00'), $computed[1]?->getStart());
        $this->assertEquals(new \DateTime('2013-03-01 17:00:00'), $computed[2]?->getStart());
        $this->assertEquals(new \DateTime('2013-03-02 02:00:00'), $computed[3]?->getStart());
        $this->assertEquals(new \DateTime('2013-03-02 11:00:00'), $computed[4]?->getStart());
    }

    public function testHourlyLeapYear(): void
    {
        $rule = new Rule(
            'FREQ=HOURLY;COUNT=5;',
            new \DateTime('2016-02-28 23:00:00')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new \DateTime('2016-02-28 23:00:00'), $computed[0]?->getStart());
        $this->assertEquals(new \DateTime('2016-02-29 00:00:00'), $computed[1]?->getStart());
        $this->assertEquals(new \DateTime('2016-02-29 01:00:00'), $computed[2]?->getStart());
        $this->assertEquals(new \DateTime('2016-02-29 02:00:00'), $computed[3]?->getStart());
        $this->assertEquals(new \DateTime('2016-02-29 03:00:00'), $computed[4]?->getStart());
    }

    public function testHourlyCrossingYears(): void
    {
        $rule = new Rule(
            'FREQ=HOURLY;COUNT=5;',
            new \DateTime('2013-12-31 22:00:00')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new \DateTime('2013-12-31 22:00:00'), $computed[0]?->getStart());
        $this->assertEquals(new \DateTime('2013-12-31 23:00:00'), $computed[1]?->getStart());
        $this->assertEquals(new \DateTime('2014-01-01 00:00:00'), $computed[2]?->getStart());
        $this->assertEquals(new \DateTime('2014-01-01 01:00:00'), $computed[3]?->getStart());
        $this->assertEquals(new \DateTime('2014-01-01 02:00:00'), $computed[4]?->getStart());
    }
}

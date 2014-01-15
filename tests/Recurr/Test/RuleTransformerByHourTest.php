<?php

namespace Recurr\Test;

use Recurr\Rule;

class RuleTransformerByHourTest extends RuleTransformerBase
{
    public function testByHourHourly()
    {
        $rule = new Rule(
            'FREQ=HOURLY;COUNT=5;BYHOUR=14,15',
            new \DateTime('2013-06-12 16:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-06-13 14:00:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-06-13 15:00:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-06-14 14:00:00'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-06-14 15:00:00'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-06-15 14:00:00'), $computed[4]);
    }

    public function testByHourDaily()
    {
        $rule = new Rule(
            'FREQ=DAILY;COUNT=5;BYHOUR=14,15',
            new \DateTime('2013-06-12 16:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-06-13 14:00:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-06-13 15:00:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-06-14 14:00:00'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-06-14 15:00:00'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-06-15 14:00:00'), $computed[4]);
    }

    public function testByHourWeekly()
    {
        $rule = new Rule(
            'FREQ=WEEKLY;COUNT=5;BYHOUR=14,15',
            new \DateTime('2013-06-12 16:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-06-19 14:00:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-06-19 15:00:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-06-26 14:00:00'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-06-26 15:00:00'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-07-03 14:00:00'), $computed[4]);
    }

    public function testByHourMonthly()
    {
        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=5;BYHOUR=14,15',
            new \DateTime('2013-06-12 16:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-07-12 14:00:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-07-12 15:00:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-08-12 14:00:00'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-08-12 15:00:00'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-09-12 14:00:00'), $computed[4]);
    }

    public function testByHourMonthlyLeapYear()
    {
        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=5;BYHOUR=14,15',
            new \DateTime('2016-01-29 12:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2016-01-29 14:00:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2016-01-29 15:00:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2016-02-29 14:00:00'), $computed[2]);
        $this->assertEquals(new \DateTime('2016-02-29 15:00:00'), $computed[3]);
        $this->assertEquals(new \DateTime('2016-03-29 14:00:00'), $computed[4]);
    }

    public function testByHourYearly()
    {
        $rule = new Rule(
            'FREQ=YEARLY;COUNT=5;BYHOUR=14,15',
            new \DateTime('2013-06-12 16:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2014-06-12 14:00:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2014-06-12 15:00:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2015-06-12 14:00:00'), $computed[2]);
        $this->assertEquals(new \DateTime('2015-06-12 15:00:00'), $computed[3]);
        $this->assertEquals(new \DateTime('2016-06-12 14:00:00'), $computed[4]);
    }

    public function testByHourYearlyLeapYear()
    {
        $rule = new Rule(
            'FREQ=YEARLY;COUNT=5;BYHOUR=14,15',
            new \DateTime('2016-02-29 12:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2016-02-29 14:00:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2016-02-29 15:00:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2017-02-28 14:00:00'), $computed[2]);
        $this->assertEquals(new \DateTime('2017-02-28 15:00:00'), $computed[3]);
        $this->assertEquals(new \DateTime('2018-02-28 14:00:00'), $computed[4]);
    }
}

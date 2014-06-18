<?php

namespace Recurr\Test\Transformer;

use Recurr\Rule;
use Recurr\Transformer\ArrayTransformerConfig;

class ArrayTransformerMonthlyTest extends ArrayTransformerBase
{
    public function testMonthly()
    {
        $timezone = 'America/New_York';
        $timezoneObj = new \DateTimeZone($timezone);

        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=3;INTERVAL=1',
            new \DateTime('2013-01-31 00:00:00', $timezoneObj),
            null,
            $timezone
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(3, $computed);
        $this->assertEquals(new \DateTime('2013-01-31 00:00:00', $timezoneObj), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2013-03-31 00:00:00', $timezoneObj), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2013-05-31 00:00:00', $timezoneObj), $computed[2]->getStart());
    }

    public function testMonthlyWithLastDayFixEnabled()
    {
        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=10',
            new \DateTime('2013-11-30')
        );

        $transformerConfig = new ArrayTransformerConfig();
        $transformerConfig->enableLastDayOfMonthFix();

        $this->transformer->setConfig($transformerConfig);
        $computed = $this->transformer->transform($rule);

        $this->assertCount(10, $computed);
        $this->assertEquals(new \DateTime('2013-11-30'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2013-12-30'), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2014-01-30'), $computed[2]->getStart());
        $this->assertEquals(new \DateTime('2014-02-28'), $computed[3]->getStart());
        $this->assertEquals(new \DateTime('2014-03-30'), $computed[4]->getStart());
        $this->assertEquals(new \DateTime('2014-04-30'), $computed[5]->getStart());
        $this->assertEquals(new \DateTime('2014-05-30'), $computed[6]->getStart());
        $this->assertEquals(new \DateTime('2014-06-30'), $computed[7]->getStart());
        $this->assertEquals(new \DateTime('2014-07-30'), $computed[8]->getStart());
        $this->assertEquals(new \DateTime('2014-08-30'), $computed[9]->getStart());
    }

    public function testMonthlyWithLastDayFixEnabledOnLeapYear()
    {
        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=8',
            new \DateTime('2016-01-31')
        );

        $transformerConfig = new ArrayTransformerConfig();
        $transformerConfig->enableLastDayOfMonthFix();

        $this->transformer->setConfig($transformerConfig);
        $computed = $this->transformer->transform($rule);

        $this->assertCount(8, $computed);
        $this->assertEquals(new \DateTime('2016-01-31'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2016-02-29'), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2016-03-31'), $computed[2]->getStart());
        $this->assertEquals(new \DateTime('2016-04-30'), $computed[3]->getStart());
        $this->assertEquals(new \DateTime('2016-05-31'), $computed[4]->getStart());
        $this->assertEquals(new \DateTime('2016-06-30'), $computed[5]->getStart());
        $this->assertEquals(new \DateTime('2016-07-31'), $computed[6]->getStart());
        $this->assertEquals(new \DateTime('2016-08-31'), $computed[7]->getStart());
    }

    public function testLastDayOfMonth()
    {
        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=5;BYMONTHDAY=28,29,30,31;BYSETPOS=-1',
            new \DateTime('2016-01-29')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new \DateTime('2016-01-31'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2016-02-29'), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2016-03-31'), $computed[2]->getStart());
        $this->assertEquals(new \DateTime('2016-04-30'), $computed[3]->getStart());
        $this->assertEquals(new \DateTime('2016-05-31'), $computed[4]->getStart());
    }
}

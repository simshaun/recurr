<?php

namespace Recurr\Test;

use Recurr\Rule;

class RuleTransformerYearlyTest extends RuleTransformerBase
{
    public function testYearly()
    {
        $timezone = new \DateTimeZone('America/New_York');

        $rule = new Rule(
            'FREQ=YEARLY;COUNT=3;INTERVAL=1',
            new \DateTime('2013-06-13 00:00:00', $timezone)
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(3, count($computed));
        $this->assertEquals(new \DateTime('2013-06-13 00:00:00', $timezone), $computed[0]);
        $this->assertEquals(new \DateTime('2014-06-13 00:00:00', $timezone), $computed[1]);
        $this->assertEquals(new \DateTime('2015-06-13 00:00:00', $timezone), $computed[2]);
    }

    public function testYearlyLeapYear()
    {
        $timezone = new \DateTimeZone('America/New_York');

        $rule = new Rule(
            'FREQ=YEARLY;COUNT=5;INTERVAL=1',
            new \DateTime('2016-02-29 00:00:00', $timezone)
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2016-02-29 00:00:00', $timezone), $computed[0]);
        $this->assertEquals(new \DateTime('2017-02-28 00:00:00', $timezone), $computed[1]);
        $this->assertEquals(new \DateTime('2018-02-28 00:00:00', $timezone), $computed[2]);
        $this->assertEquals(new \DateTime('2019-02-28 00:00:00', $timezone), $computed[3]);
        $this->assertEquals(new \DateTime('2020-02-29 00:00:00', $timezone), $computed[4]);
    }
}

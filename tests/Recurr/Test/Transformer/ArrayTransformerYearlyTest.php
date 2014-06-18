<?php

namespace Recurr\Test\Transformer;

use Recurr\Rule;

class ArrayTransformerYearlyTest extends ArrayTransformerBase
{
    public function testYearly()
    {
        $timezone = new \DateTimeZone('America/New_York');

        $rule = new Rule(
            'FREQ=YEARLY;COUNT=3;INTERVAL=1',
            new \DateTime('2013-06-13 00:00:00', $timezone)
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(3, $computed);
        $this->assertEquals(new \DateTime('2013-06-13 00:00:00', $timezone), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2014-06-13 00:00:00', $timezone), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2015-06-13 00:00:00', $timezone), $computed[2]->getStart());
    }

    public function testYearlyLeapYear()
    {
        $timezone = new \DateTimeZone('America/New_York');

        $rule = new Rule(
            'FREQ=YEARLY;COUNT=5;INTERVAL=1',
            new \DateTime('2016-02-29 00:00:00', $timezone)
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new \DateTime('2016-02-29 00:00:00', $timezone), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2017-02-28 00:00:00', $timezone), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2018-02-28 00:00:00', $timezone), $computed[2]->getStart());
        $this->assertEquals(new \DateTime('2019-02-28 00:00:00', $timezone), $computed[3]->getStart());
        $this->assertEquals(new \DateTime('2020-02-29 00:00:00', $timezone), $computed[4]->getStart());
    }
}

<?php

namespace Recurr\Test\Transformer;

use Recurr\Rule;

class ArrayTransformerExDateTest extends ArrayTransformerBase
{
    public function testExDateNoTime()
    {
        $rule = new Rule(
            'FREQ=DAILY;COUNT=3;EXDATE=20140602',
            new \DateTime('2014-06-01')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(2, $computed);
        $this->assertEquals(new \DateTime('2014-06-01'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2014-06-03'), $computed[1]->getStart());
    }

    public function testExDateWithTime()
    {
        $rule = new Rule(
            'FREQ=DAILY;COUNT=3;EXDATE=20140603T040000',
            new \DateTime('2014-06-01 04:00:00')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(2, $computed);
        $this->assertEquals(new \DateTime('2014-06-01 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2014-06-02 04:00:00'), $computed[1]->getStart());
    }

    public function testExDateWithUtcTime()
    {
        $rule = new Rule(
            'FREQ=DAILY;COUNT=3;EXDATE=20140602T080000Z',
            new \DateTime('2014-06-01 04:00:00')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(2, $computed);
        $this->assertEquals(new \DateTime('2014-06-01 04:00:00'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2014-06-03 04:00:00'), $computed[1]->getStart());
    }

    public function testExDateWithMixedTimezones()
    {
        $rule = new Rule(
            'FREQ=DAILY;COUNT=3;EXDATE=20140601T040000,20140602T080000Z',
            new \DateTime('2014-06-01 04:00:00')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(1, $computed);
        $this->assertEquals(new \DateTime('2014-06-03 04:00:00'), $computed[0]->getStart());
    }
}

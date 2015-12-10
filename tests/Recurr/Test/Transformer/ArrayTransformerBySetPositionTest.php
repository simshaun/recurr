<?php

namespace Recurr\Test\Transformer;

use Recurr\Rule;
use Recurr\Transformer\ArrayTransformerConfig;

class ArrayTransformerBySetPositionTest extends ArrayTransformerBase
{
    public function testBySetPosition()
    {
        $rule = new Rule(
            'FREQ=MONTHLY;BYSETPOS=-1;BYDAY=MO,TU,WE,TH,FR;COUNT=5',
            new \DateTime('2013-01-24')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new \DateTime('2013-01-31'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2013-02-28'), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2013-03-29'), $computed[2]->getStart());
        $this->assertEquals(new \DateTime('2013-04-30'), $computed[3]->getStart());
        $this->assertEquals(new \DateTime('2013-05-31'), $computed[4]->getStart());

        // --------------------------------------

        $rule = new Rule(
            'FREQ=MONTHLY;BYSETPOS=-1;BYDAY=MO,TU,WE,TH,FR;COUNT=5',
            new \DateTime('2016-01-24')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new \DateTime('2016-01-29'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2016-02-29'), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2016-03-31'), $computed[2]->getStart());
        $this->assertEquals(new \DateTime('2016-04-29'), $computed[3]->getStart());
        $this->assertEquals(new \DateTime('2016-05-31'), $computed[4]->getStart());

        // --------------------------------------

        $rule = new Rule(
            'FREQ=MONTHLY;BYSETPOS=1,-1;BYDAY=MO,TU,WE,TH,FR;COUNT=5',
            new \DateTime('2016-01-24')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new \DateTime('2016-01-29'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2016-02-01'), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2016-02-29'), $computed[2]->getStart());
        $this->assertEquals(new \DateTime('2016-03-01'), $computed[3]->getStart());
        $this->assertEquals(new \DateTime('2016-03-31'), $computed[4]->getStart());
    }

    public function testBySetPositionVirtualLimit()
    {
        $rule = new Rule(
            'FREQ=MONTHLY;BYSETPOS=-1;BYDAY=MO,TU,WE,TH,FR',
            new \DateTime('2013-01-24')
        );

        $config = new ArrayTransformerConfig();
        $config->setVirtualLimit(5);
        $this->transformer->setConfig($config);

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new \DateTime('2013-01-31'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2013-02-28'), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2013-03-29'), $computed[2]->getStart());
        $this->assertEquals(new \DateTime('2013-04-30'), $computed[3]->getStart());
        $this->assertEquals(new \DateTime('2013-05-31'), $computed[4]->getStart());
    }

    public function testBySetPositionWithInterval()
    {
        $rule = new Rule(
            'FREQ=MONTHLY;INTERVAL=2;BYDAY=MO;BYSETPOS=2;COUNT=10',
            new \DateTime('2013-10-09')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(10, $computed);
        $this->assertEquals(new \DateTime('2013-10-14'), $computed[0]->getStart());
        $this->assertEquals(new \DateTime('2013-12-09'), $computed[1]->getStart());
        $this->assertEquals(new \DateTime('2014-02-10'), $computed[2]->getStart());
        $this->assertEquals(new \DateTime('2014-04-14'), $computed[3]->getStart());
        $this->assertEquals(new \DateTime('2014-06-09'), $computed[4]->getStart());
        $this->assertEquals(new \DateTime('2014-08-11'), $computed[5]->getStart());
        $this->assertEquals(new \DateTime('2014-10-13'), $computed[6]->getStart());
        $this->assertEquals(new \DateTime('2014-12-08'), $computed[7]->getStart());
        $this->assertEquals(new \DateTime('2015-02-09'), $computed[8]->getStart());
        $this->assertEquals(new \DateTime('2015-04-13'), $computed[9]->getStart());
    }
}

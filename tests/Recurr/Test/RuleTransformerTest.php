<?php

namespace Recurr\Test;

use Recurr\Rule;

class RuleTransformerTest extends RuleTransformerBase
{
    public function testVirtualLimit()
    {
        $rule = new Rule(
            'FREQ=YEARLY;COUNT=30',
            new \DateTime('2014-03-16 04:00:00')
        );

        $this->transformer->setRule($rule);
        $this->transformer->setVirtualLimit(5);
        $computed = $this->transformer->getComputedArray();

        $this->assertCount(5, $computed);
    }

    public function testUntil()
    {
        $rule = new Rule(
            'FREQ=YEARLY;UNTIL=20160316T040000',
            new \DateTime('2014-03-16 04:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertCount(3, $computed);
        $this->assertEquals(new \DateTime('2014-03-16 04:00:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2015-03-16 04:00:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2016-03-16 04:00:00'), $computed[2]);
    }

    public function testDtend()
    {
        $rule = new Rule(
            'FREQ=YEARLY;DTEND=20160316T040000',
            new \DateTime('2014-03-16 04:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertCount(3, $computed);
        $this->assertEquals(new \DateTime('2014-03-16 04:00:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2015-03-16 04:00:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2016-03-16 04:00:00'), $computed[2]);
    }

    public function testRfc2445Example()
    {
        $rule = new Rule(
            'FREQ=YEARLY;INTERVAL=2;BYMONTH=1;BYDAY=SU;BYHOUR=8,9;COUNT=30',
            new \DateTime('1997-01-05 08:30:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(30, count($computed));
        $this->assertEquals(new \DateTime('1997-01-05 08:30:00'), $computed[0]);
        $this->assertEquals(new \DateTime('1997-01-05 09:30:00'), $computed[1]);
        $this->assertEquals(new \DateTime('1997-01-12 08:30:00'), $computed[2]);
        $this->assertEquals(new \DateTime('1997-01-12 09:30:00'), $computed[3]);
        $this->assertEquals(new \DateTime('1997-01-19 08:30:00'), $computed[4]);
        $this->assertEquals(new \DateTime('1997-01-19 09:30:00'), $computed[5]);
        $this->assertEquals(new \DateTime('1997-01-26 08:30:00'), $computed[6]);
        $this->assertEquals(new \DateTime('1997-01-26 09:30:00'), $computed[7]);
        $this->assertEquals(new \DateTime('1999-01-03 08:30:00'), $computed[8]);
        $this->assertEquals(new \DateTime('1999-01-03 09:30:00'), $computed[9]);
        $this->assertEquals(new \DateTime('1999-01-10 08:30:00'), $computed[10]);
        $this->assertEquals(new \DateTime('1999-01-10 09:30:00'), $computed[11]);
        $this->assertEquals(new \DateTime('1999-01-17 08:30:00'), $computed[12]);
        $this->assertEquals(new \DateTime('1999-01-17 09:30:00'), $computed[13]);
        $this->assertEquals(new \DateTime('1999-01-24 08:30:00'), $computed[14]);
        $this->assertEquals(new \DateTime('1999-01-24 09:30:00'), $computed[15]);
        $this->assertEquals(new \DateTime('1999-01-31 08:30:00'), $computed[16]);
        $this->assertEquals(new \DateTime('1999-01-31 09:30:00'), $computed[17]);
        $this->assertEquals(new \DateTime('2001-01-07 08:30:00'), $computed[18]);
        $this->assertEquals(new \DateTime('2001-01-07 09:30:00'), $computed[19]);
        $this->assertEquals(new \DateTime('2001-01-14 08:30:00'), $computed[20]);
        $this->assertEquals(new \DateTime('2001-01-14 09:30:00'), $computed[21]);
        $this->assertEquals(new \DateTime('2001-01-21 08:30:00'), $computed[22]);
        $this->assertEquals(new \DateTime('2001-01-21 09:30:00'), $computed[23]);
        $this->assertEquals(new \DateTime('2001-01-28 08:30:00'), $computed[24]);
        $this->assertEquals(new \DateTime('2001-01-28 09:30:00'), $computed[25]);
        $this->assertEquals(new \DateTime('2003-01-05 08:30:00'), $computed[26]);
        $this->assertEquals(new \DateTime('2003-01-05 09:30:00'), $computed[27]);
        $this->assertEquals(new \DateTime('2003-01-12 08:30:00'), $computed[28]);
        $this->assertEquals(new \DateTime('2003-01-12 09:30:00'), $computed[29]);
    }
}

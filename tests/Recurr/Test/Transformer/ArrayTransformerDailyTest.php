<?php

namespace Recurr\Test\Transformer;

use Recurr\Rule;

class ArrayTransformerDailyTest extends ArrayTransformerBase
{
    public function testDaily()
    {
        $timezone = new \DateTimeZone('Europe/Amsterdam');

        $rule = new Rule(
            'FREQ=DAILY;COUNT=20;INTERVAL=1',
            new \DateTime('2016-02-20 00:00:00', $timezone)
        );

        // Populate expected result array
        $expectedArray = array();
        for ($i = 0; $i < 20; $i++) {
            $date = new \DateTime('2016-02-20 +'.$i.' days');
            $date->setTime(0,0,0);
            $expectedArray[] = $date;
        }

        $computed = $this->transformer->transform($rule);

        $computedDates = array();

        foreach ($computed->toArray() as $recurr) {
            $date = clone $recurr->getStart();
            $date->setTime(0,0,0);
            $computedDates[] = $recurr->getStart();
        }

        $this->assertEquals($expectedArray, $computedDates);
    }
}
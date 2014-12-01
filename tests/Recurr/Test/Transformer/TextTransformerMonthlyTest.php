<?php

namespace Recurr\Test\Transformer;

use Recurr\Rule;

class TextTransformerMonthlyTest extends TextTransformerBase
{
    public function testMonthly()
    {
        $rule = new Rule('FREQ=MONTHLY', $this->dateTime);
        $this->assertEquals('every month', $this->transformer->transform($rule));
    }

    public function testMonthlyPlural()
    {
        $rule = new Rule('FREQ=MONTHLY;INTERVAL=10', $this->dateTime);
        $this->assertEquals('every 10 months', $this->transformer->transform($rule));
    }

    public function testMonthlyByMonth()
    {
        $rule = new Rule('FREQ=MONTHLY;BYMONTH=1,8,5', $this->dateTime);
        $this->assertEquals('every January, May and August', $this->transformer->transform($rule));
        $this->transformer->resetFragments();

        $rule = new Rule('FREQ=MONTHLY;INTERVAL=2;BYMONTH=1,8,5', $this->dateTime);
        $this->assertEquals('every 2 months in January, May and August', $this->transformer->transform($rule));
    }

    public function testMonthlyByMonthDay()
    {
        $rule = new Rule('FREQ=MONTHLY;BYMONTHDAY=5,1,21', $this->dateTime);
        $this->assertEquals('every month on the 1st, 5th and 21st', $this->transformer->transform($rule));
        $this->transformer->resetFragments();

        $rule = new Rule('FREQ=MONTHLY;BYMONTHDAY=5,1,21;BYDAY=TU,FR', $this->dateTime);
        $this->assertEquals(
            'every month on Tuesday or Friday the 1st, 5th or 21st',
            $this->transformer->transform($rule)
        );
    }

    public function testMonthlyByDay()
    {
        $rule = new Rule('FREQ=MONTHLY;BYDAY=TU,WE,FR', $this->dateTime);
        $this->assertEquals('every month on Tuesday, Wednesday and Friday', $this->transformer->transform($rule));

        $rule = new Rule('FREQ=MONTHLY;BYDAY=+4MO', $this->dateTime);
        $this->assertEquals(
            'every month on the 4th Monday',
            $this->transformer->transform($rule)
        );

        $rule = new Rule('FREQ=MONTHLY;BYDAY=+4MO,+2TU', $this->dateTime);
        $this->assertEquals(
            'every month on the 4th Monday and 2nd Tuesday',
            $this->transformer->transform($rule)
        );

        $rule = new Rule('FREQ=MONTHLY;BYDAY=+4MO,+2TU,+3WE', $this->dateTime);
        $this->assertEquals(
            'every month on the 4th Monday, 2nd Tuesday and 3rd Wednesday',
            $this->transformer->transform($rule)
        );

        $rule = new Rule('FREQ=MONTHLY;BYDAY=1MO,+1TU,+2WE,+3WE,+4WE,-1TH,-2FR,-3SA,-4SU', $this->dateTime);
        $this->assertEquals(
            'every month on the 1st Monday, 1st Tuesday, 2nd Wednesday, 3rd Wednesday, 4th Wednesday, last Thursday, 2nd last Friday, 3rd last Saturday and 4th last Sunday',
            $this->transformer->transform($rule)
        );
    }
}

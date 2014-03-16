<?php

namespace Recurr\Test\Transformer;

use Recurr\Rule;

class TextTransformerYearlyTest extends TextTransformerBase
{
    public function testYearly()
    {
        $rule = new Rule('FREQ=YEARLY', $this->dateTime);
        $this->assertEquals('every year', $this->transformer->transform($rule));
    }

    public function testYearlyPlural()
    {
        $rule = new Rule('FREQ=YEARLY;INTERVAL=10', $this->dateTime);
        $this->assertEquals('every 10 years', $this->transformer->transform($rule));
    }

    public function testYearlyByMonth()
    {
        $rule = new Rule('FREQ=YEARLY;BYMONTH=1,8,5', $this->dateTime);
        $this->assertEquals('every January, May and August', $this->transformer->transform($rule));
        $this->transformer->resetFragments();

        $rule = new Rule('FREQ=YEARLY;INTERVAL=2;BYMONTH=1,8,5', $this->dateTime);
        $this->assertEquals('every 2 years in January, May and August', $this->transformer->transform($rule));
    }

    public function testYearlyByMonthDay()
    {
        $rule = new Rule('FREQ=YEARLY;BYMONTHDAY=5,1,21', $this->dateTime);
        $this->assertEquals('every year on the 1st, 5th and 21st', $this->transformer->transform($rule));
        $this->transformer->resetFragments();

        $rule = new Rule('FREQ=YEARLY;BYMONTHDAY=5,1,21;BYDAY=TU,FR', $this->dateTime);
        $this->assertEquals(
            'every year on Tuesday or Friday the 1st, 5th or 21st',
            $this->transformer->transform($rule)
        );
    }

    public function testYearlyByDay()
    {
        $rule = new Rule('FREQ=YEARLY;BYDAY=TU,WE,FR', $this->dateTime);
        $this->assertEquals('every year on Tuesday, Wednesday and Friday', $this->transformer->transform($rule));
    }

    public function testYearlyByYearDay()
    {
        $rule = new Rule('FREQ=YEARLY;BYYEARDAY=1,200', $this->dateTime);
        $this->assertEquals('every year on the 1st and 200th day', $this->transformer->transform($rule));
    }

    public function testYearlyByWeekNumber()
    {
        $rule = new Rule('FREQ=YEARLY;BYWEEKNO=3', $this->dateTime);
        $this->assertEquals('every year in week 3', $this->transformer->transform($rule));
        $this->transformer->resetFragments();

        $rule = new Rule('FREQ=YEARLY;BYWEEKNO=3,30,20', $this->dateTime);
        $this->assertEquals('every year in weeks 3, 20 and 30', $this->transformer->transform($rule));
    }
}

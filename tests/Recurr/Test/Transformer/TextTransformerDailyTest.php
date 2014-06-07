<?php

namespace Recurr\Test\Transformer;

use Recurr\Rule;

class TextTransformerDailyTest extends TextTransformerBase
{
    public function testDaily()
    {
        $rule = new Rule('FREQ=DAILY', $this->dateTime);
        $this->assertEquals('every day', $this->transformer->transform($rule));
    }

    public function testDailyPlural()
    {
        $rule = new Rule('FREQ=DAILY;INTERVAL=10', $this->dateTime);
        $this->assertEquals('every 10 days', $this->transformer->transform($rule));
    }

    public function testDailyByMonth()
    {
        $rule = new Rule('FREQ=DAILY;BYMONTH=1,8,5', $this->dateTime);
        $this->assertEquals('every day in January, May and August', $this->transformer->transform($rule));
        $this->transformer->resetFragments();

        $rule = new Rule('FREQ=DAILY;INTERVAL=2;BYMONTH=1,8,5', $this->dateTime);
        $this->assertEquals('every 2 days in January, May and August', $this->transformer->transform($rule));
    }

    public function testDailyByMonthDay()
    {
        $rule = new Rule('FREQ=DAILY;BYMONTHDAY=5,1,21', $this->dateTime);
        $this->assertEquals('every day on the 1st, 5th and 21st', $this->transformer->transform($rule));
        $this->transformer->resetFragments();

        $rule = new Rule('FREQ=DAILY;BYMONTHDAY=5,1,21;BYDAY=TU,FR', $this->dateTime);
        $this->assertEquals(
            'every day on Tuesday or Friday the 1st, 5th or 21st',
            $this->transformer->transform($rule)
        );
    }

    public function testDailyByDay()
    {
        $rule = new Rule('FREQ=DAILY;BYDAY=TU,WE,FR', $this->dateTime);
        $this->assertEquals('every day on Tuesday, Wednesday and Friday', $this->transformer->transform($rule));
    }
}

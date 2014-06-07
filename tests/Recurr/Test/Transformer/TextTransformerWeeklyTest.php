<?php

namespace Recurr\Test\Transformer;

use Recurr\Rule;

class TextTransformerWeeklyTest extends TextTransformerBase
{
    public function testWeekly()
    {
        $rule = new Rule('FREQ=WEEKLY', $this->dateTime);
        $this->assertEquals('every week', $this->transformer->transform($rule));
    }

    public function testWeeklyPlural()
    {
        $rule = new Rule('FREQ=WEEKLY;INTERVAL=10', $this->dateTime);
        $this->assertEquals('every 10 weeks', $this->transformer->transform($rule));
    }

    public function testWeeklyByMonth()
    {
        $rule = new Rule('FREQ=WEEKLY;BYMONTH=1,8,5', $this->dateTime);
        $this->assertEquals('every week in January, May and August', $this->transformer->transform($rule));
        $this->transformer->resetFragments();

        $rule = new Rule('FREQ=WEEKLY;INTERVAL=2;BYMONTH=1,8,5', $this->dateTime);
        $this->assertEquals('every 2 weeks in January, May and August', $this->transformer->transform($rule));
    }

    public function testWeeklyByMonthDay()
    {
        $rule = new Rule('FREQ=WEEKLY;BYMONTHDAY=5,1,21', $this->dateTime);
        $this->assertEquals('every week on the 1st, 5th and 21st', $this->transformer->transform($rule));
        $this->transformer->resetFragments();

        $rule = new Rule('FREQ=WEEKLY;BYMONTHDAY=5,1,21;BYDAY=TU,FR', $this->dateTime);
        $this->assertEquals(
            'every week on Tuesday or Friday the 1st, 5th or 21st',
            $this->transformer->transform($rule)
        );
    }

    public function testWeeklyByDay()
    {
        $rule = new Rule('FREQ=WEEKLY;BYDAY=TU,WE,FR', $this->dateTime);
        $this->assertEquals('every week on Tuesday, Wednesday and Friday', $this->transformer->transform($rule));
    }
}

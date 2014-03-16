<?php

namespace Recurr\Test\Transformer;

use Recurr\Rule;

class TextTransformerBaseTest extends TextTransformerBase
{
    public function testCount()
    {
        $rule = new Rule('FREQ=YEARLY;COUNT=1', $this->dateTime);
        $this->assertEquals('every year for 1 time', $this->transformer->transform($rule));
    }

    public function testCountPlural()
    {
        $rule = new Rule('FREQ=YEARLY;COUNT=3', $this->dateTime);
        $this->assertEquals('every year for 3 times', $this->transformer->transform($rule));
    }

    public function testUntil()
    {
        $rule = new Rule('FREQ=YEARLY;UNTIL=20140704T040000Z', $this->dateTime);
        $this->assertEquals('every year until July 4, 2014', $this->transformer->transform($rule));
    }

    public function testFullyConvertible()
    {
        $rule = new Rule('FREQ=YEARLY;BYHOUR=1', $this->dateTime);
        $this->assertEquals('every year (~ approximate)', $this->transformer->transform($rule));
        $this->transformer->resetFragments();

        $rule = new Rule('FREQ=YEARLY;BYMINUTE=1', $this->dateTime);
        $this->assertEquals('every year (~ approximate)', $this->transformer->transform($rule));
        $this->transformer->resetFragments();

        $rule = new Rule('FREQ=YEARLY;BYSECOND=1', $this->dateTime);
        $this->assertEquals('every year (~ approximate)', $this->transformer->transform($rule));
        $this->transformer->resetFragments();

        $rule = new Rule('FREQ=MONTHLY;BYWEEKNO=50', $this->dateTime);
        $this->assertEquals('every month (~ approximate)', $this->transformer->transform($rule));
        $this->transformer->resetFragments();

        $rule = new Rule('FREQ=MONTHLY;BYYEARDAY=200', $this->dateTime);
        $this->assertEquals('every month (~ approximate)', $this->transformer->transform($rule));
        $this->transformer->resetFragments();
    }
}

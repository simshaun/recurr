<?php

namespace Recurr\Test;

use Recurr\RecurrenceRule;
use Recurr\Exception\InvalidArgument;
use Recurr\Exception\InvalidRRule;

class RecurrenceRuleTest extends \PHPUnit_Framework_TestCase
{
    /** @var RecurrenceRule */
    protected $rule;

    public function setUp()
    {
        $this->rule = new RecurrenceRule;
    }

    /**
     * @expectedException \Recurr\Exception\InvalidRRule
     */
    public function testCreateFromStringWithMissingFreq()
    {
        $this->rule->createFromString('COUNT=2');
    }

    /**
     * @expectedException \Recurr\Exception\InvalidRRule
     */
    public function testCreateFromStringWithBothCountAndUntil()
    {
        $this->rule->createFromString('FREQ=DAILY;COUNT=2;UNTIL=20130510');
    }

    public function testCreateFromString()
    {
        $string = 'FREQ=YEARLY;';
        $string .= 'COUNT=2;';
        $string .= 'INTERVAL=2;';
        $string .= 'BYSECOND=30;';
        $string .= 'BYMINUTE=10;';
        $string .= 'BYHOUR=5,15;';
        $string .= 'BYDAY=SU,WE;';
        $string .= 'BYMONTHDAY=16,22;';
        $string .= 'BYYEARDAY=201,203;';
        $string .= 'BYWEEKNO=29,32;';
        $string .= 'BYMONTH=7,8;';
        $string .= 'BYSETPOS=1,3;';
        $string .= 'WKST=TU;';

        $this->rule->createFromString($string);

        $this->assertEquals(RecurrenceRule::FREQ_YEARLY, $this->rule->getFreq());
        $this->assertEquals(2, $this->rule->getCount());
        $this->assertEquals(2, $this->rule->getInterval());
        $this->assertEquals(array(30), $this->rule->getBySecond());
        $this->assertEquals(array(10), $this->rule->getByMinute());
        $this->assertEquals(array(5, 15), $this->rule->getByHour());
        $this->assertEquals(array('SU', 'WE'), $this->rule->getByDay());
        $this->assertEquals(array(16, 22), $this->rule->getByMonthDay());
        $this->assertEquals(array(201, 203), $this->rule->getByYearDay());
        $this->assertEquals(array(29, 32), $this->rule->getByWeekNumber());
        $this->assertEquals(array(7, 8), $this->rule->getByMonth());
        $this->assertEquals(array(1, 3), $this->rule->getBySetPosition());
        $this->assertEquals('TU', $this->rule->getWeekStart());
    }

    /**
     * @expectedException \Recurr\Exception\InvalidRRule
     */
    public function testCreateFromStringFails()
    {
        $this->rule->createFromString('IM AN INVALID RRULE');
    }

    public function testGetString()
    {
        $this->rule->setFreq('YEARLY');
        $this->rule->setCount(2);
        $this->rule->setInterval(2);
        $this->rule->setBySecond(array(30));
        $this->rule->setByMinute(array(10));
        $this->rule->setByHour(array(5, 15));
        $this->rule->setByDay(array('SU', 'WE'));
        $this->rule->setByMonthDay(array(16, 22));
        $this->rule->setByYearDay(array(201, 203));
        $this->rule->setByWeekNumber(array(29, 32));
        $this->rule->setByMonth(array(7, 8));
        $this->rule->setBySetPosition(array(1, 3));
        $this->rule->setWeekStart('TU');

        $this->assertEquals(
            'FREQ=YEARLY;COUNT=2;INTERVAL=2;BYSECOND=30;BYMINUTE=10;BYHOUR=5,15;BYDAY=SU,WE;BYMONTHDAY=16,22;BYYEARDAY=201,203;BYWEEKNO=29,32;BYMONTH=7,8;BYSETPOS=1,3;WKST=TU',
            $this->rule->getString()
        );
    }

    /**
     * @expectedException \Recurr\Exception\InvalidArgument
     */
    public function testBadInterval()
    {
        $this->rule->setInterval('six');
    }

    /**
     * @expectedException \Recurr\Exception\InvalidArgument
     */
    public function testBadWeekStart()
    {
        $this->rule->setWeekStart('monday');
    }
}
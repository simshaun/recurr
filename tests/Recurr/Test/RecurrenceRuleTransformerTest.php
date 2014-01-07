<?php

namespace Recurr\Test;

use Recurr\RecurrenceRule;
use Recurr\RecurrenceRuleTransformer;

class RecurrenceRuleTransformerTest extends \PHPUnit_Framework_TestCase
{
    /** @var RecurrenceRuleTransformer */
    protected $transformer;

    protected $timezone = 'America/New_York';

    public function setUp()
    {
        $this->transformer = new RecurrenceRuleTransformer;
    }

    public function testSecondly()
    {
        $rule = new RecurrenceRule(
            'FREQ=SECONDLY;COUNT=5;',
            new \DateTime('2016-02-29 23:58:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2016-02-29 23:58:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2016-02-29 23:58:01'), $computed[1]);
        $this->assertEquals(new \DateTime('2016-02-29 23:58:02'), $computed[2]);
        $this->assertEquals(new \DateTime('2016-02-29 23:58:03'), $computed[3]);
        $this->assertEquals(new \DateTime('2016-02-29 23:58:04'), $computed[4]);
    }

    public function testSecondlyInterval()
    {
        $rule = new RecurrenceRule(
            'FREQ=SECONDLY;COUNT=5;INTERVAL=58;',
            new \DateTime('2016-02-29 23:58:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2016-02-29 23:58:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2016-02-29 23:58:58'), $computed[1]);
        $this->assertEquals(new \DateTime('2016-02-29 23:59:56'), $computed[2]);
        $this->assertEquals(new \DateTime('2016-03-01 00:00:54'), $computed[3]);
        $this->assertEquals(new \DateTime('2016-03-01 00:01:52'), $computed[4]);
    }

    public function testMinutely()
    {
        $rule = new RecurrenceRule(
            'FREQ=MINUTELY;COUNT=5;',
            new \DateTime('2016-02-29 23:58:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2016-02-29 23:58:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2016-02-29 23:59:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2016-03-01 00:00:00'), $computed[2]);
        $this->assertEquals(new \DateTime('2016-03-01 00:01:00'), $computed[3]);
        $this->assertEquals(new \DateTime('2016-03-01 00:02:00'), $computed[4]);
    }

    public function testMinutelyInterval()
    {
        $rule = new RecurrenceRule(
            'FREQ=MINUTELY;COUNT=5;INTERVAL=58;',
            new \DateTime('2013-02-28 23:58:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-02-28 23:58:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-02-29 00:56:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-02-29 01:54:00'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-02-29 02:52:00'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-02-29 03:50:00'), $computed[4]);
    }

    public function testHourly()
    {
        $rule = new RecurrenceRule(
            'FREQ=HOURLY;COUNT=5;',
            new \DateTime('2013-02-28 23:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-02-28 23:00:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-03-01 00:00:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-03-01 01:00:00'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-03-01 02:00:00'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-03-01 03:00:00'), $computed[4]);
    }

    public function testHourlyInterval()
    {
        $rule = new RecurrenceRule(
            'FREQ=HOURLY;COUNT=5;INTERVAL=9;',
            new \DateTime('2013-02-28 23:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-02-28 23:00:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-03-01 08:00:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-03-01 17:00:00'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-03-02 02:00:00'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-03-02 11:00:00'), $computed[4]);
    }

    public function testHourlyLeapYear()
    {
        $rule = new RecurrenceRule(
            'FREQ=HOURLY;COUNT=5;',
            new \DateTime('2016-02-28 23:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2016-02-28 23:00:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2016-02-29 00:00:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2016-02-29 01:00:00'), $computed[2]);
        $this->assertEquals(new \DateTime('2016-02-29 02:00:00'), $computed[3]);
        $this->assertEquals(new \DateTime('2016-02-29 03:00:00'), $computed[4]);
    }

    public function testHourlyCrossingYears()
    {
        $rule = new RecurrenceRule(
            'FREQ=HOURLY;COUNT=5;',
            new \DateTime('2013-12-31 22:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-12-31 22:00:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-12-31 23:00:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2014-01-01 00:00:00'), $computed[2]);
        $this->assertEquals(new \DateTime('2014-01-01 01:00:00'), $computed[3]);
        $this->assertEquals(new \DateTime('2014-01-01 02:00:00'), $computed[4]);
    }

    public function testWeekly()
    {
        $timezone = 'America/New_York';
        $timezoneObj = new \DateTimeZone($timezone);

        $rule = new RecurrenceRule(
            'FREQ=WEEKLY;COUNT=5;INTERVAL=1',
            new \DateTime('2013-06-13 00:00:00', $timezoneObj),
            $timezone
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-06-13 00:00:00', $timezoneObj), $computed[0]);
        $this->assertEquals(new \DateTime('2013-06-20 00:00:00', $timezoneObj), $computed[1]);
        $this->assertEquals(new \DateTime('2013-06-27 00:00:00', $timezoneObj), $computed[2]);
        $this->assertEquals(new \DateTime('2013-07-04 00:00:00', $timezoneObj), $computed[3]);
        $this->assertEquals(new \DateTime('2013-07-11 00:00:00', $timezoneObj), $computed[4]);
    }

    public function testWeeklyInterval()
    {
        $timezone = 'America/New_York';
        $timezoneObj = new \DateTimeZone($timezone);

        $rule = new RecurrenceRule(
            'FREQ=WEEKLY;COUNT=5;INTERVAL=2',
            new \DateTime('2013-12-19 00:00:00', $timezoneObj),
            $timezone
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-12-19 00:00:00', $timezoneObj), $computed[0]);
        $this->assertEquals(new \DateTime('2014-01-02 00:00:00', $timezoneObj), $computed[1]);
        $this->assertEquals(new \DateTime('2014-01-16 00:00:00', $timezoneObj), $computed[2]);
        $this->assertEquals(new \DateTime('2014-01-30 00:00:00', $timezoneObj), $computed[3]);
        $this->assertEquals(new \DateTime('2014-02-13 00:00:00', $timezoneObj), $computed[4]);
    }

    public function testWeeklyIntervalLeapYear()
    {
        $timezone = 'America/New_York';
        $timezoneObj = new \DateTimeZone($timezone);

        $rule = new RecurrenceRule(
            'FREQ=WEEKLY;COUNT=7;INTERVAL=2',
            new \DateTime('2015-12-21 00:00:00', $timezoneObj),
            $timezone
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(7, count($computed));
        $this->assertEquals(new \DateTime('2015-12-21 00:00:00', $timezoneObj), $computed[0]);
        $this->assertEquals(new \DateTime('2016-01-04 00:00:00', $timezoneObj), $computed[1]);
        $this->assertEquals(new \DateTime('2016-01-18 00:00:00', $timezoneObj), $computed[2]);
        $this->assertEquals(new \DateTime('2016-02-01 00:00:00', $timezoneObj), $computed[3]);
        $this->assertEquals(new \DateTime('2016-02-15 00:00:00', $timezoneObj), $computed[4]);
        $this->assertEquals(new \DateTime('2016-02-29 00:00:00', $timezoneObj), $computed[5]);
        $this->assertEquals(new \DateTime('2016-03-14 00:00:00', $timezoneObj), $computed[6]);
    }

    public function testWeeklyIntervalHittingJan1()
    {
        $timezone = 'America/New_York';
        $timezoneObj = new \DateTimeZone($timezone);

        $rule = new RecurrenceRule(
            'FREQ=WEEKLY;COUNT=3;INTERVAL=2',
            new \DateTime('2013-12-18 00:00:00', $timezoneObj),
            $timezone
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(3, count($computed));
        $this->assertEquals(new \DateTime('2013-12-18 00:00:00', $timezoneObj), $computed[0]);
        $this->assertEquals(new \DateTime('2014-01-01 00:00:00', $timezoneObj), $computed[1]);
        $this->assertEquals(new \DateTime('2014-01-15 00:00:00', $timezoneObj), $computed[2]);
    }

    public function testMonthly()
    {
        $timezone = 'America/New_York';
        $timezoneObj = new \DateTimeZone($timezone);

        $rule = new RecurrenceRule(
            'FREQ=MONTHLY;COUNT=3;INTERVAL=1',
            new \DateTime('2013-01-31 00:00:00', $timezoneObj),
            $timezone
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(3, count($computed));
        $this->assertEquals(new \DateTime('2013-01-31 00:00:00', $timezoneObj), $computed[0]);
        $this->assertEquals(new \DateTime('2013-03-31 00:00:00', $timezoneObj), $computed[1]);
        $this->assertEquals(new \DateTime('2013-05-31 00:00:00', $timezoneObj), $computed[2]);
    }

    public function testYearly()
    {
        $timezone = new \DateTimeZone('America/New_York');

        $rule = new RecurrenceRule(
            'FREQ=YEARLY;COUNT=3;INTERVAL=1',
            new \DateTime('2013-06-13 00:00:00', $timezone)
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(3, count($computed));
        $this->assertEquals(new \DateTime('2013-06-13 00:00:00', $timezone), $computed[0]);
        $this->assertEquals(new \DateTime('2014-06-13 00:00:00', $timezone), $computed[1]);
        $this->assertEquals(new \DateTime('2015-06-13 00:00:00', $timezone), $computed[2]);
    }

    public function testYearlyLeapYear()
    {
        $timezone = new \DateTimeZone('America/New_York');

        $rule = new RecurrenceRule(
            'FREQ=YEARLY;COUNT=5;INTERVAL=1',
            new \DateTime('2016-02-29 00:00:00', $timezone)
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2016-02-29 00:00:00', $timezone), $computed[0]);
        $this->assertEquals(new \DateTime('2017-02-28 00:00:00', $timezone), $computed[1]);
        $this->assertEquals(new \DateTime('2018-02-28 00:00:00', $timezone), $computed[2]);
        $this->assertEquals(new \DateTime('2019-02-28 00:00:00', $timezone), $computed[3]);
        $this->assertEquals(new \DateTime('2020-02-29 00:00:00', $timezone), $computed[4]);
    }

    public function testByMinute()
    {
        $rule = new RecurrenceRule(
            'FREQ=HOURLY;COUNT=5;BYMINUTE=14,15',
            new \DateTime('2013-06-12 16:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-06-12 16:14:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-06-12 16:15:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-06-12 17:14:00'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-06-12 17:15:00'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-06-12 18:14:00'), $computed[4]);
    }

    public function testBySecond()
    {
        $rule = new RecurrenceRule(
            'FREQ=HOURLY;COUNT=5;BYSECOND=36,45',
            new \DateTime('2013-06-12 16:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-06-12 16:00:36'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-06-12 16:00:45'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-06-12 17:00:36'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-06-12 17:00:45'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-06-12 18:00:36'), $computed[4]);
    }

    public function testMinutelyBySecond()
    {
        $rule = new RecurrenceRule(
            'FREQ=MINUTELY;COUNT=5;BYSECOND=36,45',
            new \DateTime('2013-06-12 16:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-06-12 16:00:36'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-06-12 16:00:45'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-06-12 16:01:36'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-06-12 16:01:45'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-06-12 16:02:36'), $computed[4]);
    }

    public function testByHour()
    {
        $rule = new RecurrenceRule(
            'FREQ=HOURLY;COUNT=5;BYHOUR=14,15',
            new \DateTime('2013-06-12 16:00:00')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-06-13 14:00:00'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-06-13 15:00:00'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-06-14 14:00:00'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-06-14 15:00:00'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-06-15 14:00:00'), $computed[4]);
    }

    public function testByMonth()
    {
        $rule = new RecurrenceRule(
            'FREQ=DAILY;COUNT=4;BYMONTH=2,3',
            new \DateTime('2013-02-26')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(4, count($computed));
        $this->assertEquals(new \DateTime('2013-02-26'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-02-27'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-02-28'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-03-01'), $computed[3]);
    }

    public function testByMonthLeapYear()
    {
        $rule = new RecurrenceRule(
            'FREQ=DAILY;COUNT=4;BYMONTH=2,3',
            new \DateTime('2016-02-27')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(4, count($computed));
        $this->assertEquals(new \DateTime('2016-02-27'), $computed[0]);
        $this->assertEquals(new \DateTime('2016-02-28'), $computed[1]);
        $this->assertEquals(new \DateTime('2016-02-29'), $computed[2]);
        $this->assertEquals(new \DateTime('2016-03-01'), $computed[3]);
    }

    public function testLastDayOfMonth()
    {
        $rule = new RecurrenceRule(
            'FREQ=MONTHLY;COUNT=5;BYMONTHDAY=28,29,30,31;BYSETPOS=-1',
            new \DateTime('2016-01-29')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2016-01-31'), $computed[0]);
        $this->assertEquals(new \DateTime('2016-02-29'), $computed[1]);
        $this->assertEquals(new \DateTime('2016-03-31'), $computed[2]);
        $this->assertEquals(new \DateTime('2016-04-30'), $computed[3]);
        $this->assertEquals(new \DateTime('2016-05-31'), $computed[4]);
    }

    public function testByWeekNumber()
    {
        $rule = new RecurrenceRule(
            'FREQ=YEARLY;COUNT=5;BYWEEKNO=22;WKST=SU',
            new \DateTime('2013-05-30')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-05-30'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-05-31'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-06-01'), $computed[2]);
        $this->assertEquals(new \DateTime('2014-05-25'), $computed[3]);
        $this->assertEquals(new \DateTime('2014-05-26'), $computed[4]);
    }

    public function testByWeekNumberNegative()
    {
        $rule = new RecurrenceRule(
            'FREQ=YEARLY;COUNT=5;BYWEEKNO=-44;WKST=TH',
            new \DateTime('2013-05-30')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2014-02-27'), $computed[0]);
        $this->assertEquals(new \DateTime('2014-02-28'), $computed[1]);
        $this->assertEquals(new \DateTime('2014-03-01'), $computed[2]);
        $this->assertEquals(new \DateTime('2014-03-02'), $computed[3]);
        $this->assertEquals(new \DateTime('2014-03-03'), $computed[4]);
    }

    public function testByWeekNumberWeek53()
    {
        $rule = new RecurrenceRule(
            'FREQ=YEARLY;COUNT=5;BYWEEKNO=53;WKST=MO',
            new \DateTime('2013-05-30')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2015-12-28'), $computed[0]);
        $this->assertEquals(new \DateTime('2015-12-29'), $computed[1]);
        $this->assertEquals(new \DateTime('2015-12-30'), $computed[2]);
        $this->assertEquals(new \DateTime('2015-12-31'), $computed[3]);
        $this->assertEquals(new \DateTime('2016-01-01'), $computed[4]);
    }

    public function testByWeekNumberWeek53Negative()
    {
        $rule = new RecurrenceRule(
            'FREQ=YEARLY;COUNT=5;BYWEEKNO=-53;WKST=MO',
            new \DateTime('2008-05-30')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2009-01-01'), $computed[0]);
        $this->assertEquals(new \DateTime('2009-01-02'), $computed[1]);
        $this->assertEquals(new \DateTime('2009-01-03'), $computed[2]);
        $this->assertEquals(new \DateTime('2009-01-04'), $computed[3]);
        $this->assertEquals(new \DateTime('2015-01-01'), $computed[4]);
    }

    public function testByYearDay()
    {
        $rule = new RecurrenceRule(
            'FREQ=YEARLY;COUNT=4;BYYEARDAY=125',
            new \DateTime('2013-01-02')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(4, count($computed));
        $this->assertEquals(new \DateTime('2013-05-05'), $computed[0]);
        $this->assertEquals(new \DateTime('2014-05-05'), $computed[1]);
        $this->assertEquals(new \DateTime('2015-05-05'), $computed[2]);
        $this->assertEquals(new \DateTime('2016-05-04'), $computed[3]);
    }

    public function testByYearDayNegative()
    {
        $rule = new RecurrenceRule(
            'FREQ=YEARLY;COUNT=4;BYYEARDAY=-307',
            new \DateTime('2013-06-07')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(4, count($computed));
        $this->assertEquals(new \DateTime('2014-02-28'), $computed[0]);
        $this->assertEquals(new \DateTime('2015-02-28'), $computed[1]);
        $this->assertEquals(new \DateTime('2016-02-29'), $computed[2]);
        $this->assertEquals(new \DateTime('2017-02-28'), $computed[3]);
    }

    public function testByMonthDay()
    {
        $rule = new RecurrenceRule(
            'FREQ=MONTHLY;COUNT=5;BYMONTHDAY=28,29,30',
            new \DateTime('2013-01-30')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-01-30'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-02-28'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-03-28'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-03-29'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-03-30'), $computed[4]);
    }

    public function testByMonthDayLeapYear()
    {
        $rule = new RecurrenceRule(
            'FREQ=MONTHLY;COUNT=5;BYMONTHDAY=28,29,30',
            new \DateTime('2016-01-30')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2016-01-30'), $computed[0]);
        $this->assertEquals(new \DateTime('2016-02-28'), $computed[1]);
        $this->assertEquals(new \DateTime('2016-02-29'), $computed[2]);
        $this->assertEquals(new \DateTime('2016-03-28'), $computed[3]);
        $this->assertEquals(new \DateTime('2016-03-29'), $computed[4]);
    }

    public function testByMonthDayNegative()
    {
        $rule = new RecurrenceRule(
            'FREQ=MONTHLY;COUNT=5;BYMONTHDAY=-10',
            new \DateTime('2013-06-07')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-06-21'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-07-22'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-08-22'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-09-21'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-10-22'), $computed[4]);
    }

    public function testByMonthDayPositiveAndNegative()
    {
        $rule = new RecurrenceRule(
            'FREQ=MONTHLY;COUNT=5;BYMONTHDAY=15,-1',
            new \DateTime('2013-10-01')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-10-15'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-10-31'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-11-15'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-11-30'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-12-15'), $computed[4]);
    }

    public function testByWeekDay()
    {
        $rule = new RecurrenceRule(
            'FREQ=WEEKLY;COUNT=5;BYDAY=MO,TU',
            new \DateTime('2013-01-28')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-01-28'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-01-29'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-02-04'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-02-05'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-02-11'), $computed[4]);
    }

    public function testWeeklyByWeekDayWithInterval2()
    {
        $rule = new RecurrenceRule(
            'FREQ=WEEKLY;COUNT=5;INTERVAL=2;BYDAY=TU,WE;WKST=WE',
            new \DateTime('2013-01-23')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-01-23'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-01-29'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-02-06'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-02-12'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-02-20'), $computed[4]);

        // --------------------------------

        $rule = new RecurrenceRule(
            'FREQ=WEEKLY;COUNT=5;INTERVAL=2;BYDAY=TU,WE;WKST=SA',
            new \DateTime('2013-01-24')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-02-05'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-02-06'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-02-19'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-02-20'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-03-05'), $computed[4]);
    }

    public function testMonthlyByWeekDayRelativeFromStart()
    {
        $rule = new RecurrenceRule(
            'FREQ=MONTHLY;COUNT=5;BYDAY=+1MO',
            new \DateTime('2013-06-04')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-07-01'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-08-05'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-09-02'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-10-07'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-11-04'), $computed[4]);
    }

    public function testMonthlyByWeekDayRelativeFromEnd()
    {
        $rule = new RecurrenceRule(
            'FREQ=MONTHLY;COUNT=5;BYDAY=-1MO',
            new \DateTime('2013-06-04')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-06-24'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-07-29'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-08-26'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-09-30'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-10-28'), $computed[4]);

        // -----------------------

        $rule = new RecurrenceRule(
            'FREQ=MONTHLY;COUNT=5;BYDAY=-5MO',
            new \DateTime('2013-06-05')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-07-01'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-09-02'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-12-02'), $computed[2]);
        $this->assertEquals(new \DateTime('2014-03-03'), $computed[3]);
        $this->assertEquals(new \DateTime('2014-06-02'), $computed[4]);
    }

    public function testYearlyByWeekDayRelativeFromStart()
    {
        $rule = new RecurrenceRule(
            'FREQ=YEARLY;COUNT=5;BYDAY=+1MO',
            new \DateTime('2013-06-04')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2014-01-06'), $computed[0]);
        $this->assertEquals(new \DateTime('2015-01-05'), $computed[1]);
        $this->assertEquals(new \DateTime('2016-01-04'), $computed[2]);
        $this->assertEquals(new \DateTime('2017-01-02'), $computed[3]);
        $this->assertEquals(new \DateTime('2018-01-01'), $computed[4]);
    }

    public function testYearlyByWeekDayRelativeFromEnd()
    {
        $rule = new RecurrenceRule(
            'FREQ=YEARLY;COUNT=5;BYDAY=-1MO',
            new \DateTime('2013-06-04')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-12-30'), $computed[0]);
        $this->assertEquals(new \DateTime('2014-12-29'), $computed[1]);
        $this->assertEquals(new \DateTime('2015-12-28'), $computed[2]);
        $this->assertEquals(new \DateTime('2016-12-26'), $computed[3]);
        $this->assertEquals(new \DateTime('2017-12-25'), $computed[4]);
    }

    public function testMonthlyByWeekDayRelativeFromStartAndEnd()
    {
        $rule = new RecurrenceRule(
            'FREQ=MONTHLY;COUNT=10;BYDAY=-1MO,3FR',
            new \DateTime('2013-06-04')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(10, count($computed));
        $this->assertEquals(new \DateTime('2013-06-21'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-06-24'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-07-19'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-07-29'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-08-16'), $computed[4]);
        $this->assertEquals(new \DateTime('2013-08-26'), $computed[5]);
        $this->assertEquals(new \DateTime('2013-09-20'), $computed[6]);
        $this->assertEquals(new \DateTime('2013-09-30'), $computed[7]);
        $this->assertEquals(new \DateTime('2013-10-18'), $computed[8]);
        $this->assertEquals(new \DateTime('2013-10-28'), $computed[9]);
    }

    public function testMonthlyByWeekDayWithSundayStartOfYear()
    {
        $rule = new RecurrenceRule(
            'FREQ=MONTHLY;COUNT=6;BYDAY=MO',
            new \DateTime('2017-01-01')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(6, count($computed));
        $this->assertEquals(new \DateTime('2017-01-02'), $computed[0]);
        $this->assertEquals(new \DateTime('2017-01-09'), $computed[1]);
        $this->assertEquals(new \DateTime('2017-01-16'), $computed[2]);
        $this->assertEquals(new \DateTime('2017-01-23'), $computed[3]);
        $this->assertEquals(new \DateTime('2017-01-30'), $computed[4]);
        $this->assertEquals(new \DateTime('2017-02-06'), $computed[5]);

        // -----------------------------------

        $rule = new RecurrenceRule(
            'FREQ=MONTHLY;COUNT=6;BYDAY=-3MO,3FR',
            new \DateTime('2017-01-01')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(6, count($computed));
        $this->assertEquals(new \DateTime('2017-01-16'), $computed[0]);
        $this->assertEquals(new \DateTime('2017-01-20'), $computed[1]);
        $this->assertEquals(new \DateTime('2017-02-13'), $computed[2]);
        $this->assertEquals(new \DateTime('2017-02-17'), $computed[3]);
        $this->assertEquals(new \DateTime('2017-03-13'), $computed[4]);
        $this->assertEquals(new \DateTime('2017-03-17'), $computed[5]);
    }

    public function testBySetPosition()
    {
        $rule = new RecurrenceRule(
            'FREQ=MONTHLY;BYSETPOS=-1;BYDAY=MO,TU,WE,TH,FR;COUNT=5',
            new \DateTime('2013-01-24')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2013-01-31'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-02-28'), $computed[1]);
        $this->assertEquals(new \DateTime('2013-03-29'), $computed[2]);
        $this->assertEquals(new \DateTime('2013-04-30'), $computed[3]);
        $this->assertEquals(new \DateTime('2013-05-31'), $computed[4]);

        // --------------------------------------

        $rule = new RecurrenceRule(
            'FREQ=MONTHLY;BYSETPOS=-1;BYDAY=MO,TU,WE,TH,FR;COUNT=5',
            new \DateTime('2016-01-24')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2016-01-29'), $computed[0]);
        $this->assertEquals(new \DateTime('2016-02-29'), $computed[1]);
        $this->assertEquals(new \DateTime('2016-03-31'), $computed[2]);
        $this->assertEquals(new \DateTime('2016-04-29'), $computed[3]);
        $this->assertEquals(new \DateTime('2016-05-31'), $computed[4]);

        // --------------------------------------

        $rule = new RecurrenceRule(
            'FREQ=MONTHLY;BYSETPOS=1,-1;BYDAY=MO,TU,WE,TH,FR;COUNT=5',
            new \DateTime('2016-01-24')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(5, count($computed));
        $this->assertEquals(new \DateTime('2016-01-29'), $computed[0]);
        $this->assertEquals(new \DateTime('2016-02-01'), $computed[1]);
        $this->assertEquals(new \DateTime('2016-02-29'), $computed[2]);
        $this->assertEquals(new \DateTime('2016-03-01'), $computed[3]);
        $this->assertEquals(new \DateTime('2016-03-31'), $computed[4]);
    }

    public function testBySetPositionWithInterval()
    {
        $rule = new RecurrenceRule(
            'FREQ=MONTHLY;INTERVAL=2;BYDAY=MO;BYSETPOS=2;COUNT=10',
            new \DateTime('2013-10-09')
        );

        $this->transformer->setRule($rule);
        $computed = $this->transformer->getComputedArray();

        $this->assertEquals(10, count($computed));
        $this->assertEquals(new \DateTime('2013-10-14'), $computed[0]);
        $this->assertEquals(new \DateTime('2013-12-09'), $computed[1]);
        $this->assertEquals(new \DateTime('2014-02-10'), $computed[2]);
        $this->assertEquals(new \DateTime('2014-04-14'), $computed[3]);
        $this->assertEquals(new \DateTime('2014-06-09'), $computed[4]);
        $this->assertEquals(new \DateTime('2014-08-11'), $computed[5]);
        $this->assertEquals(new \DateTime('2014-10-13'), $computed[6]);
        $this->assertEquals(new \DateTime('2014-12-08'), $computed[7]);
        $this->assertEquals(new \DateTime('2015-02-09'), $computed[8]);
        $this->assertEquals(new \DateTime('2015-04-13'), $computed[9]);
    }

    public function testRfc2445Example()
    {
        $rule = new RecurrenceRule(
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

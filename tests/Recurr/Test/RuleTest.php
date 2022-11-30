<?php

namespace Recurr\Test;

use PHPUnit\Framework\TestCase;
use Recurr\DateExclusion;
use Recurr\DateInclusion;
use Recurr\Exception\InvalidArgument;
use Recurr\Exception\InvalidRRule;
use Recurr\Frequency;
use Recurr\Rule;
use TypeError;

class RuleTest extends TestCase
{
    private array $defaults = ['FREQ' => 'DAILY'];

    public function testConstructAcceptableStartDate(): void
    {
        $rule = new Rule($this->defaults, null);
        $this->assertNull($rule->getStartDate());

        $rule = new Rule($this->defaults, '2018-09-19');
        $this->assertInstanceOf(\DateTime::class, $rule->getStartDate());

        $rule = new Rule($this->defaults, new \DateTime('2018-09-19'));
        $this->assertInstanceOf(\DateTime::class, $rule->getStartDate());
    }

    public function testConstructAcceptableEndDate(): void
    {
        $rule = new Rule($this->defaults, null, '2018-09-19');
        $this->assertInstanceOf(\DateTime::class, $rule->getEndDate());

        $rule = new Rule($this->defaults, null, new \DateTime('2018-09-19'));
        $this->assertInstanceOf(\DateTime::class, $rule->getEndDate());
    }

    public function testDefaultTimezone(): void
    {
        $this->assertEquals(date_default_timezone_get(), Rule::createFromArray($this->defaults)->getTimezone());
    }

    public function testTimezoneObtainedFromStartDate(): void
    {
        $startDate = new \DateTime('2014-01-25 05:20:30', new \DateTimeZone('America/Los_Angeles'));

        $rule = new Rule($this->defaults, $startDate);
        $this->assertEquals($startDate->getTimezone()->getName(), $rule->getTimezone());
    }

    public function testLoadFromStringWithMissingFreq(): void
    {
        $this->expectException(InvalidRRule::class);
        Rule::createFromString('COUNT=2');
    }

    public function testLoadFromStringWithBothCountAndUntil(): void
    {
        $this->expectException(InvalidRRule::class);
        Rule::createFromString('FREQ=DAILY;COUNT=2;UNTIL=20130510');
    }

    public function testLoadFromString(): void
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
        $string .= 'RDATE=20151210,20151214T020000,20151215T210000Z;';
        $string .= 'EXDATE=20140607,20140620T010000,20140620T160000Z;';

        $rule = Rule::createFromString($string);

        $this->assertEquals(Frequency::YEARLY, $rule->getFreq());
        $this->assertEquals(2, $rule->getCount());
        $this->assertEquals(2, $rule->getInterval());
        $this->assertEquals([30], $rule->getBySecond());
        $this->assertEquals([10], $rule->getByMinute());
        $this->assertEquals([5, 15], $rule->getByHour());
        $this->assertEquals(['SU', 'WE'], $rule->getByDay());
        $this->assertEquals([16, 22], $rule->getByMonthDay());
        $this->assertEquals([201, 203], $rule->getByYearDay());
        $this->assertEquals([29, 32], $rule->getByWeekNumber());
        $this->assertEquals([7, 8], $rule->getByMonth());
        $this->assertEquals([1, 3], $rule->getBySetPosition());
        $this->assertEquals('TU', $rule->getWeekStart());
        $this->assertEquals(
            [
                new DateInclusion(new \DateTime(20151210), false),
                new DateInclusion(new \DateTime('20151214T020000'), true),
                new DateInclusion(new \DateTime('20151215 21:00:00 UTC'), true, true)
            ],
            $rule->getRDates()
        );
        $this->assertEquals(
            [
                new DateExclusion(new \DateTime(20140607), false),
                new DateExclusion(new \DateTime('20140620T010000'), true),
                new DateExclusion(new \DateTime('20140620 16:00:00 UTC'), true, true)
            ],
            $rule->getExDates()
        );
    }

    public function testLoadFromStringWithDtStartDirective(): void
    {
        $string = 'DTSTART:20190102';
        $string .= Rule::LINE_SEPARATOR;
        $string .= 'RRULE:';
        $string .= 'FREQ=YEARLY;';
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
        $string .= 'RDATE=20151210,20151214T020000,20151215T210000Z;';
        $string .= 'EXDATE=20140607,20140620T010000,20140620T160000Z;';

        $rule = Rule::createFromString($string);

        $this->assertEquals(Frequency::YEARLY, $rule->getFreq());
        $this->assertEquals(new \DateTime('2019-01-02'), $rule->getStartDate());
        $this->assertEquals(2, $rule->getCount());
        $this->assertEquals(2, $rule->getInterval());
        $this->assertEquals([30], $rule->getBySecond());
        $this->assertEquals([10], $rule->getByMinute());
        $this->assertEquals([5, 15], $rule->getByHour());
        $this->assertEquals(['SU', 'WE'], $rule->getByDay());
        $this->assertEquals([16, 22], $rule->getByMonthDay());
        $this->assertEquals([201, 203], $rule->getByYearDay());
        $this->assertEquals([29, 32], $rule->getByWeekNumber());
        $this->assertEquals([7, 8], $rule->getByMonth());
        $this->assertEquals([1, 3], $rule->getBySetPosition());
        $this->assertEquals('TU', $rule->getWeekStart());
        $this->assertEquals(
            [
                new DateInclusion(new \DateTime(20151210), false),
                new DateInclusion(new \DateTime('20151214T020000'), true),
                new DateInclusion(new \DateTime('20151215 21:00:00 UTC'), true, true)
            ],
            $rule->getRDates()
        );
        $this->assertEquals(
            [
                new DateExclusion(new \DateTime(20140607), false),
                new DateExclusion(new \DateTime('20140620T010000'), true),
                new DateExclusion(new \DateTime('20140620 16:00:00 UTC'), true, true)
            ],
            $rule->getExDates()
        );
    }

    public function testLoadFromStringWithRruleDirective(): void
    {
        $string = 'RRULE:';
        $string .= 'FREQ=YEARLY;';
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
        $string .= 'RDATE=20151210,20151214T020000,20151215T210000Z;';
        $string .= 'EXDATE=20140607,20140620T010000,20140620T160000Z;';

        $rule = Rule::createFromString($string);

        $this->assertEquals(Frequency::YEARLY, $rule->getFreq());
        $this->assertEquals(2, $rule->getCount());
        $this->assertEquals(2, $rule->getInterval());
        $this->assertEquals([30], $rule->getBySecond());
        $this->assertEquals([10], $rule->getByMinute());
        $this->assertEquals([5, 15], $rule->getByHour());
        $this->assertEquals(['SU', 'WE'], $rule->getByDay());
        $this->assertEquals([16, 22], $rule->getByMonthDay());
        $this->assertEquals([201, 203], $rule->getByYearDay());
        $this->assertEquals([29, 32], $rule->getByWeekNumber());
        $this->assertEquals([7, 8], $rule->getByMonth());
        $this->assertEquals([1, 3], $rule->getBySetPosition());
        $this->assertEquals('TU', $rule->getWeekStart());
        $this->assertEquals(
            [
                new DateInclusion(new \DateTime(20151210), false),
                new DateInclusion(new \DateTime('20151214T020000'), true),
                new DateInclusion(new \DateTime('20151215 21:00:00 UTC'), true, true)
            ],
            $rule->getRDates()
        );
        $this->assertEquals(
            [
                new DateExclusion(new \DateTime(20140607), false),
                new DateExclusion(new \DateTime('20140620T010000'), true),
                new DateExclusion(new \DateTime('20140620 16:00:00 UTC'), true, true)
            ],
            $rule->getExDates()
        );
    }

    public function testLoadFromArray(): void
    {
        $rule = Rule::createFromArray([
            'FREQ' => 'YEARLY',
            'COUNT' => '2',
            'INTERVAL' => '2',
            'BYSECOND' => '30',
            'BYMINUTE' => '10',
            'BYHOUR' => '5,15',
            'BYDAY' => 'SU,WE',
            'BYMONTHDAY' => '16,22',
            'BYYEARDAY' => '201,203',
            'BYWEEKNO' => '29,32',
            'BYMONTH' => '7,8',
            'BYSETPOS' => '1,3',
            'WKST' => 'TU',
            'RDATE' => '20151210,20151214T020000,20151215T210000Z',
            'EXDATE' => '20140607,20140620T010000,20140620T160000Z',
        ]);

        $this->assertEquals(Frequency::YEARLY, $rule->getFreq());
        $this->assertEquals(2, $rule->getCount());
        $this->assertEquals(2, $rule->getInterval());
        $this->assertEquals([30], $rule->getBySecond());
        $this->assertEquals([10], $rule->getByMinute());
        $this->assertEquals([5, 15], $rule->getByHour());
        $this->assertEquals(['SU', 'WE'], $rule->getByDay());
        $this->assertEquals([16, 22], $rule->getByMonthDay());
        $this->assertEquals([201, 203], $rule->getByYearDay());
        $this->assertEquals([29, 32], $rule->getByWeekNumber());
        $this->assertEquals([7, 8], $rule->getByMonth());
        $this->assertEquals([1, 3], $rule->getBySetPosition());
        $this->assertEquals('TU', $rule->getWeekStart());
        $this->assertEquals(
            [
                new DateInclusion(new \DateTime(20151210), false),
                new DateInclusion(new \DateTime('20151214T020000'), true),
                new DateInclusion(new \DateTime('20151215 21:00:00 UTC'), true, true)
            ],
            $rule->getRDates()
        );
        $this->assertEquals(
            [
                new DateExclusion(new \DateTime(20140607), false),
                new DateExclusion(new \DateTime('20140620T010000'), true),
                new DateExclusion(new \DateTime('20140620 16:00:00 UTC'), true, true)
            ],
            $rule->getExDates()
        );
    }


    public function testLoadFromStringWithDtstart(): void
    {
        $defaultTimezone = date_default_timezone_get();
        date_default_timezone_set('America/Chicago');

        $string = 'FREQ=MONTHLY;DTSTART=20140222T073000';

        $rule = Rule::createFromString($string);
        $rule->setTimezone('America/Los_Angeles');

        $expectedStartDate = new \DateTime('2014-02-22 05:30:00', new \DateTimeZone('America/Los_Angeles'));

        $this->assertEquals(Frequency::MONTHLY, $rule->getFreq());
        $this->assertEquals($expectedStartDate, $rule->getStartDate());

        date_default_timezone_set($defaultTimezone);
    }

    public function testLoadFromStringWithDtend(): void
    {
        $defaultTimezone = date_default_timezone_get();
        date_default_timezone_set('America/Chicago');
        $string = 'FREQ=MONTHLY;DTEND=20140422T140000';

        $rule = Rule::createFromString($string);
        $rule->setTimezone('America/Los_Angeles');

        $expectedEndDate = new \DateTime('2014-04-22 12:00:00', new \DateTimeZone('America/Los_Angeles'));

        $this->assertEquals(Frequency::MONTHLY, $rule->getFreq());
        $this->assertEquals($expectedEndDate, $rule->getEndDate());

        date_default_timezone_set($defaultTimezone);
    }

    public function testLoadFromStringFails(): void
    {
        $this->expectException(InvalidRRule::class);
        $rule = Rule::createFromString('IM AN INVALID RRULE');
    }

    public function testGetString(): void
    {
        $rule = new Rule($this->defaults);
        $rule->setFreq('YEARLY');
        $rule->setCount(2);
        $rule->setInterval(2);
        $rule->setBySecond([30]);
        $rule->setByMinute([10]);
        $rule->setByHour([5, 15]);
        $rule->setByDay(['SU', 'WE']);
        $rule->setByMonthDay([16, 22]);
        $rule->setByYearDay([201, 203]);
        $rule->setByWeekNumber([29, 32]);
        $rule->setByMonth([7, 8]);
        $rule->setBySetPosition([1, 3]);
        $rule->setWeekStart('TU');
        $rule->setRDates(['20151210', '20151214T020000Z', '20151215T210000']);
        $rule->setExDates(['20140607', '20140620T010000Z', '20140620T010000']);

        $this->assertEquals(
            'RRULE:FREQ=YEARLY;COUNT=2;INTERVAL=2;BYSECOND=30;BYMINUTE=10;BYHOUR=5,15;BYDAY=SU,WE;BYMONTHDAY=16,22;BYYEARDAY=201,203;BYWEEKNO=29,32;BYMONTH=7,8;BYSETPOS=1,3;WKST=TU;RDATE=20151210,20151214T020000Z,20151215T210000;EXDATE=20140607,20140620T010000Z,20140620T010000',
            $rule->getString()
        );
    }

    public function testGetStringWithUTC(): void
    {
        $rule = new Rule($this->defaults);
        $rule->setFreq('DAILY');
        $rule->setInterval(1);
        $rule->setUntil(new \DateTime('2015-07-10 04:00:00', new \DateTimeZone('America/New_York')));

        $this->assertNotEquals(
            'RRULE:FREQ=DAILY;UNTIL=20150710T040000Z;INTERVAL=1',
            $rule->getString()
        );

        $this->assertEquals(
            'RRULE:FREQ=DAILY;UNTIL=20150710T080000Z;INTERVAL=1',
            $rule->getString(Rule::TZ_FIXED)
        );
    }

    public function testGetStringWithDtstart(): void
    {
        $string = 'RRULE:FREQ=MONTHLY;DTSTART=20140210T163045;INTERVAL=1;WKST=MO';

        $rule = Rule::createFromString($string);

        $this->assertEquals($string, $rule->getString(inlineDates: true));
    }

    public function testGetStringWithDtend(): void
    {
        $string = 'RRULE:FREQ=MONTHLY;DTEND=20140410T163045;INTERVAL=1;WKST=MO';

        $rule = Rule::createFromString($string);

        $this->assertEquals($string, $rule->getString(inlineDates: true));
    }

    public function testGetStringWithUntil(): void
    {
        $string = 'RRULE:FREQ=MONTHLY;UNTIL=20140410T163045;INTERVAL=1;WKST=MO';

        $rule = Rule::createFromString($string);

        $this->assertEquals($string, $rule->getString());
    }

    public function testGetStringWithUntilUsingZuluTime(): void
    {
        $string = 'RRULE:FREQ=MONTHLY;UNTIL=20170331T040000Z;INTERVAL=1;WKST=MO';

        $rule = Rule::createFromString($string);

        $this->assertSame($string, $rule->getString(Rule::TZ_FIXED));
    }

    public function testGetStringWithoutExplicitWkst(): void
    {
        $string = 'FREQ=MONTHLY;COUNT=2;INTERVAL=1';

        $rule = Rule::createFromString($string);

        $this->assertStringNotContainsString('WKST', $rule->getString());
    }

    public function testGetStringWithExplicitWkst(): void
    {
        $string = 'FREQ=MONTHLY;COUNT=2;INTERVAL=1;WKST=TH';

        $rule = Rule::createFromString($string);

        $this->assertStringContainsString('WKST=TH', $rule->getString());
    }

    public function testSetStartDateAffectsStringOutput(): void
    {
        $rule = Rule::createFromString('FREQ=MONTHLY;COUNT=2');
        $this->assertEquals('RRULE:FREQ=MONTHLY;COUNT=2', $rule->getString());

        $rule->setStartDate(new \DateTime('2015-12-10'));
        $this->assertEquals('RRULE:FREQ=MONTHLY;COUNT=2', $rule->getString());

        $rule->setStartDate(new \DateTime('2015-12-10'), true);
        $this->assertEquals('RRULE:FREQ=MONTHLY;COUNT=2;DTSTART=20151210T000000', $rule->getString(inlineDates: true));

        $rule->setStartDate(new \DateTime('2015-12-10'), false);
        $this->assertEquals('RRULE:FREQ=MONTHLY;COUNT=2', $rule->getString());
    }

    public function testMultilineString(): void
    {
        $test = implode(PHP_EOL, [
            'DTSTART;TZID=Australia/Perth:20220303T110948',
            'DTEND;TZID=Australia/Perth:20220303T110948',
            'RRULE:FREQ=MONTHLY;BYSECOND=0;BYMINUTE=45;BYHOUR=9;BYDAY=MO,TU,WE,TH,FR;BYSETPOS=-3,-2,-1;WKST=MO',
        ]);
        $rule = Rule::createFromString($test);

        $this->assertEquals(strtoupper($test), $rule->getString(Rule::TZ_FIXED));
    }


    public function testBadInterval(): void
    {
        $this->expectError(TypeError::class);
        $rule->setInterval('six');
    }

    public function testEmptyByDayThrowsException(): void
    {
        $this->expectException(InvalidRRule::class);
        Rule::createFromArray($this->defaults)->setByDay([]);
    }

    public function testEmptyByDayFromStringThrowsException(): void
    {
        $this->expectException(InvalidRRule::class);
        $rule = Rule::createFromString('FREQ=WEEKLY;BYDAY=;INTERVAL=1;UNTIL=20160725');
    }

    public function testBadWeekStart(): void
    {
        $this->expectException(InvalidArgument::class);
        Rule::createFromArray($this->defaults)->setWeekStart('monday');
    }

    /**
     * @dataProvider exampleRruleProvider
     */
    public function testRepeatsIndefinitely($string, $expected)
    {
        $this->assertSame($expected, $rule = Rule::createFromString($string)->repeatsIndefinitely());
    }

    /**
     * Taken from https://tools.ietf.org/html/rfc5545#section-3.8.5.3
     *
     * @return array
     */
    public function exampleRruleProvider(): array
    {
        return [
            ['FREQ=DAILY;COUNT=10', false],
            ['FREQ=DAILY;UNTIL=19971224T000000Z', false],
            ['FREQ=DAILY;INTERVAL=2', true],
            ['FREQ=DAILY;INTERVAL=10;COUNT=5', false],
            ['FREQ=YEARLY;UNTIL=20000131T140000Z', false],
            ['FREQ=DAILY;UNTIL=20000131T140000Z;BYMONTH=1', false],
            ['FREQ=WEEKLY;COUNT=10', false],
            ['FREQ=WEEKLY;UNTIL=19971224T000000Z', false],
            ['FREQ=WEEKLY;INTERVAL=2;WKST=SU', true],
            ['FREQ=WEEKLY;UNTIL=19971007T000000Z;WKST=SU;BYDAY=TU,TH', false],
            ['FREQ=WEEKLY;COUNT=10;WKST=SU;BYDAY=TU,TH', false],
            ['FREQ=WEEKLY;INTERVAL=2;UNTIL=19971224T000000Z;WKST=SU', false],
            ['FREQ=WEEKLY;INTERVAL=2;COUNT=8;WKST=SU;BYDAY=TU,TH', false],
            ['FREQ=MONTHLY;COUNT=10;BYDAY=1FR', false],
            ['FREQ=MONTHLY;UNTIL=19971224T000000Z;BYDAY=1FR', false],
            ['FREQ=MONTHLY;INTERVAL=2;COUNT=10;BYDAY=1SU,-1SU', false],
            ['FREQ=MONTHLY;COUNT=6;BYDAY=-2MO', false],
            ['FREQ=MONTHLY;BYMONTHDAY=-3', true],
            ['FREQ=MONTHLY;COUNT=10;BYMONTHDAY=2,15', false],
            ['FREQ=MONTHLY;COUNT=10;BYMONTHDAY=1,-1', false],
            ['FREQ=MONTHLY;INTERVAL=18;COUNT=10;BYMONTHDAY=10,11,12,', false],
            ['FREQ=MONTHLY;INTERVAL=2;BYDAY=TU', true],
            ['FREQ=YEARLY;COUNT=10;BYMONTH=6,7', false],
            ['FREQ=YEARLY;INTERVAL=2;COUNT=10;BYMONTH=1,2,3', false],
            ['FREQ=YEARLY;INTERVAL=3;COUNT=10;BYYEARDAY=1,100,200', false],
            ['FREQ=YEARLY;BYDAY=20MO', true],
            ['FREQ=YEARLY;BYWEEKNO=20;BYDAY=MO', true],
            ['FREQ=YEARLY;BYMONTH=3;BYDAY=TH', true],
            ['FREQ=YEARLY;BYDAY=TH;BYMONTH=6,7,8', true],
            ['FREQ=MONTHLY;BYDAY=FR;BYMONTHDAY=13', true],
            ['FREQ=MONTHLY;BYDAY=SA;BYMONTHDAY=7,8,9,10,11,12,13', true],
            ['FREQ=YEARLY;INTERVAL=4;BYMONTH=11;BYDAY=TU', true],
            ['FREQ=MONTHLY;COUNT=3;BYDAY=TU,WE,TH;BYSETPOS=3', false],
            ['FREQ=MONTHLY;BYDAY=MO,TU,WE,TH,FR;BYSETPOS=-2', true],
            ['FREQ=HOURLY;INTERVAL=3;UNTIL=19970902T170000Z', false],
            ['FREQ=MINUTELY;INTERVAL=15;COUNT=6', false],
            ['FREQ=MINUTELY;INTERVAL=90;COUNT=4', false],
            ['FREQ=DAILY;BYHOUR=9,10,11,12,13,14,15,16;BYMINUTE=0,20,40', true],
            ['FREQ=MINUTELY;INTERVAL=20;BYHOUR=9,10,11,12,13,14,15,16', true],
            ['FREQ=WEEKLY;INTERVAL=2;COUNT=4;BYDAY=TU,SU;WKST=MO', false],
            ['FREQ=WEEKLY;INTERVAL=2;COUNT=4;BYDAY=TU,SU;WKST=SU', false],
            ['FREQ=MONTHLY;BYMONTHDAY=15,30;COUNT=5', false],
        ];
    }
}

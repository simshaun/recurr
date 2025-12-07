<?php

namespace Tests\Recurr;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Recurr\DateExclusion;
use Recurr\DateInclusion;
use Recurr\Exception\InvalidArgument;
use Recurr\Exception\InvalidRRule;
use Recurr\Frequency;
use Recurr\Rule;

class RuleTest extends TestCase
{
    protected Rule $rule;

    public function setUp(): void
    {
        $this->rule = new Rule();
    }

    public function testConstructAcceptableStartDate(): void
    {
        $this->rule = new Rule(null, null);
        $this->assertNull($this->rule->getStartDate());

        $this->rule = new Rule(null, '2018-09-19');
        $this->assertInstanceOf(\DateTime::class, $this->rule->getStartDate());

        $this->rule = new Rule(null, new \DateTime('2018-09-19'));
        $this->assertInstanceOf(\DateTime::class, $this->rule->getStartDate());
    }

    public function testConstructAcceptableEndDate(): void
    {
        $this->rule = new Rule(null, null, '2018-09-19');
        $this->assertInstanceOf(\DateTime::class, $this->rule->getEndDate());

        $this->rule = new Rule(null, null, new \DateTime('2018-09-19'));
        $this->assertInstanceOf(\DateTime::class, $this->rule->getEndDate());
    }

    public function testDefaultTimezone(): void
    {
        $this->assertEquals(date_default_timezone_get(), $this->rule->getTimezone());
    }

    public function testTimezoneObtainedFromStartDate(): void
    {
        $startDate = new \DateTime('2014-01-25 05:20:30', new \DateTimeZone('America/Los_Angeles'));

        $this->rule = new Rule(null, $startDate);
        $this->assertEquals($startDate->getTimezone()->getName(), $this->rule->getTimezone());
    }

    public function testLoadFromStringWithMissingFreq(): void
    {
        $this->expectException(InvalidRRule::class);
        $this->rule->loadFromString('COUNT=2');
    }

    public function testLoadFromStringWithBothCountAndUntil(): void
    {
        $this->expectException(InvalidRRule::class);
        $this->rule->loadFromString('FREQ=DAILY;COUNT=2;UNTIL=20130510');
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

        $this->rule->loadFromString($string);

        $this->assertEquals(Frequency::YEARLY, $this->rule->getFreq());
        $this->assertEquals(2, $this->rule->getCount());
        $this->assertEquals(2, $this->rule->getInterval());
        $this->assertEquals([30], $this->rule->getBySecond());
        $this->assertEquals([10], $this->rule->getByMinute());
        $this->assertEquals([5, 15], $this->rule->getByHour());
        $this->assertEquals(['SU', 'WE'], $this->rule->getByDay());
        $this->assertEquals([16, 22], $this->rule->getByMonthDay());
        $this->assertEquals([201, 203], $this->rule->getByYearDay());
        $this->assertEquals([29, 32], $this->rule->getByWeekNumber());
        $this->assertEquals([7, 8], $this->rule->getByMonth());
        $this->assertEquals([1, 3], $this->rule->getBySetPosition());
        $this->assertEquals('TU', $this->rule->getWeekStart());
        $this->assertEquals(
            [
                new DateInclusion(new \DateTime('20151210'), false),
                new DateInclusion(new \DateTime('20151214T020000'), true),
                new DateInclusion(new \DateTime('20151215 21:00:00 UTC'), true, true),
            ],
            $this->rule->getRDates()
        );
        $this->assertEquals(
            [
                new DateExclusion(new \DateTime('20140607'), false),
                new DateExclusion(new \DateTime('20140620T010000'), true),
                new DateExclusion(new \DateTime('20140620 16:00:00 UTC'), true, true),
            ],
            $this->rule->getExDates()
        );
    }

    public function testLoadFromStringWithDtStartDirective(): void
    {
        $string = 'DTSTART:20190102';
        $string .= "\n";
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

        $this->rule->loadFromString($string);

        $this->assertEquals(Frequency::YEARLY, $this->rule->getFreq());
        $this->assertEquals(new \DateTime('2019-01-02'), $this->rule->getStartDate());
        $this->assertEquals(2, $this->rule->getCount());
        $this->assertEquals(2, $this->rule->getInterval());
        $this->assertEquals([30], $this->rule->getBySecond());
        $this->assertEquals([10], $this->rule->getByMinute());
        $this->assertEquals([5, 15], $this->rule->getByHour());
        $this->assertEquals(['SU', 'WE'], $this->rule->getByDay());
        $this->assertEquals([16, 22], $this->rule->getByMonthDay());
        $this->assertEquals([201, 203], $this->rule->getByYearDay());
        $this->assertEquals([29, 32], $this->rule->getByWeekNumber());
        $this->assertEquals([7, 8], $this->rule->getByMonth());
        $this->assertEquals([1, 3], $this->rule->getBySetPosition());
        $this->assertEquals('TU', $this->rule->getWeekStart());
        $this->assertEquals(
            [
                new DateInclusion(new \DateTime('20151210'), false),
                new DateInclusion(new \DateTime('20151214T020000'), true),
                new DateInclusion(new \DateTime('20151215 21:00:00 UTC'), true, true),
            ],
            $this->rule->getRDates()
        );
        $this->assertEquals(
            [
                new DateExclusion(new \DateTime('20140607'), false),
                new DateExclusion(new \DateTime('20140620T010000'), true),
                new DateExclusion(new \DateTime('20140620 16:00:00 UTC'), true, true),
            ],
            $this->rule->getExDates()
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

        $this->rule->loadFromString($string);

        $this->assertEquals(Frequency::YEARLY, $this->rule->getFreq());
        $this->assertEquals(2, $this->rule->getCount());
        $this->assertEquals(2, $this->rule->getInterval());
        $this->assertEquals([30], $this->rule->getBySecond());
        $this->assertEquals([10], $this->rule->getByMinute());
        $this->assertEquals([5, 15], $this->rule->getByHour());
        $this->assertEquals(['SU', 'WE'], $this->rule->getByDay());
        $this->assertEquals([16, 22], $this->rule->getByMonthDay());
        $this->assertEquals([201, 203], $this->rule->getByYearDay());
        $this->assertEquals([29, 32], $this->rule->getByWeekNumber());
        $this->assertEquals([7, 8], $this->rule->getByMonth());
        $this->assertEquals([1, 3], $this->rule->getBySetPosition());
        $this->assertEquals('TU', $this->rule->getWeekStart());
        $this->assertEquals(
            [
                new DateInclusion(new \DateTime('20151210'), false),
                new DateInclusion(new \DateTime('20151214T020000'), true),
                new DateInclusion(new \DateTime('20151215 21:00:00 UTC'), true, true),
            ],
            $this->rule->getRDates()
        );
        $this->assertEquals(
            [
                new DateExclusion(new \DateTime('20140607'), false),
                new DateExclusion(new \DateTime('20140620T010000'), true),
                new DateExclusion(new \DateTime('20140620 16:00:00 UTC'), true, true),
            ],
            $this->rule->getExDates()
        );
    }

    public function testLoadFromArray(): void
    {
        $this->rule->loadFromArray([
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

        $this->assertEquals(Frequency::YEARLY, $this->rule->getFreq());
        $this->assertEquals(2, $this->rule->getCount());
        $this->assertEquals(2, $this->rule->getInterval());
        $this->assertEquals([30], $this->rule->getBySecond());
        $this->assertEquals([10], $this->rule->getByMinute());
        $this->assertEquals([5, 15], $this->rule->getByHour());
        $this->assertEquals(['SU', 'WE'], $this->rule->getByDay());
        $this->assertEquals([16, 22], $this->rule->getByMonthDay());
        $this->assertEquals([201, 203], $this->rule->getByYearDay());
        $this->assertEquals([29, 32], $this->rule->getByWeekNumber());
        $this->assertEquals([7, 8], $this->rule->getByMonth());
        $this->assertEquals([1, 3], $this->rule->getBySetPosition());
        $this->assertEquals('TU', $this->rule->getWeekStart());
        $this->assertEquals(
            [
                new DateInclusion(new \DateTime('20151210'), false),
                new DateInclusion(new \DateTime('20151214T020000'), true),
                new DateInclusion(new \DateTime('20151215 21:00:00 UTC'), true, true),
            ],
            $this->rule->getRDates()
        );
        $this->assertEquals(
            [
                new DateExclusion(new \DateTime('20140607'), false),
                new DateExclusion(new \DateTime('20140620T010000'), true),
                new DateExclusion(new \DateTime('20140620 16:00:00 UTC'), true, true),
            ],
            $this->rule->getExDates()
        );
    }

    public function testLoadFromStringWithDtstart(): void
    {
        $defaultTimezone = date_default_timezone_get();
        date_default_timezone_set('America/Chicago');

        $string = 'FREQ=MONTHLY;DTSTART=20140222T073000';

        $this->rule->setTimezone('America/Los_Angeles');
        $this->rule->loadFromString($string);

        $expectedStartDate = new \DateTime('2014-02-22 05:30:00', new \DateTimeZone('America/Los_Angeles'));

        $this->assertEquals(Frequency::MONTHLY, $this->rule->getFreq());
        $this->assertEquals($expectedStartDate, $this->rule->getStartDate());

        date_default_timezone_set($defaultTimezone);
    }

    public function testLoadFromStringWithDtend(): void
    {
        $defaultTimezone = date_default_timezone_get();
        date_default_timezone_set('America/Chicago');
        $string = 'FREQ=MONTHLY;DTEND=20140422T140000';

        $this->rule->setTimezone('America/Los_Angeles');
        $this->rule->loadFromString($string);

        $expectedEndDate = new \DateTime('2014-04-22 12:00:00', new \DateTimeZone('America/Los_Angeles'));

        $this->assertEquals(Frequency::MONTHLY, $this->rule->getFreq());
        $this->assertEquals($expectedEndDate, $this->rule->getEndDate());

        date_default_timezone_set($defaultTimezone);
    }

    public function testLoadFromStringFails(): void
    {
        $this->expectException(InvalidRRule::class);
        $this->rule->loadFromString('IM AN INVALID RRULE');
    }

    public function testGetString(): void
    {
        $this->rule->setFreq('YEARLY');
        $this->rule->setCount(2);
        $this->rule->setInterval(2);
        $this->rule->setBySecond([30]);
        $this->rule->setByMinute([10]);
        $this->rule->setByHour([5, 15]);
        $this->rule->setByDay(['SU', 'WE']);
        $this->rule->setByMonthDay([16, 22]);
        $this->rule->setByYearDay([201, 203]);
        $this->rule->setByWeekNumber([29, 32]);
        $this->rule->setByMonth([7, 8]);
        $this->rule->setBySetPosition([1, 3]);
        $this->rule->setWeekStart('TU');
        $this->rule->setRDates(['20151210', '20151214T020000Z', '20151215T210000']);
        $this->rule->setExDates(['20140607', '20140620T010000Z', '20140620T010000']);

        $this->assertEquals(
            'FREQ=YEARLY;COUNT=2;INTERVAL=2;BYSECOND=30;BYMINUTE=10;BYHOUR=5,15;BYDAY=SU,WE;BYMONTHDAY=16,22;BYYEARDAY=201,203;BYWEEKNO=29,32;BYMONTH=7,8;BYSETPOS=1,3;WKST=TU;RDATE=20151210,20151214T020000Z,20151215T210000;EXDATE=20140607,20140620T010000Z,20140620T010000',
            $this->rule->getString()
        );
    }

    public function testGetStringWithUTC(): void
    {
        $this->rule->setFreq('DAILY');
        $this->rule->setInterval(1);
        $this->rule->setUntil(new \DateTime('2015-07-10 04:00:00', new \DateTimeZone('America/New_York')));

        $this->assertNotEquals(
            'FREQ=DAILY;UNTIL=20150710T040000Z;INTERVAL=1',
            $this->rule->getString()
        );

        $this->assertEquals(
            'FREQ=DAILY;UNTIL=20150710T080000Z;INTERVAL=1',
            $this->rule->getString(Rule::TZ_FIXED)
        );
    }

    public function testGetStringWithDtstart(): void
    {
        $string = 'FREQ=MONTHLY;DTSTART=20140210T163045;INTERVAL=1;WKST=MO';

        $this->rule->loadFromString($string);

        $this->assertEquals($string, $this->rule->getString());
    }

    public function testGetStringWithDtend(): void
    {
        $string = 'FREQ=MONTHLY;DTEND=20140410T163045;INTERVAL=1;WKST=MO';

        $this->rule->loadFromString($string);

        $this->assertEquals($string, $this->rule->getString());
    }

    public function testGetStringWithUntil(): void
    {
        $string = 'FREQ=MONTHLY;UNTIL=20140410T163045;INTERVAL=1;WKST=MO';

        $this->rule->loadFromString($string);

        $this->assertEquals($string, $this->rule->getString());
    }

    public function testGetStringWithUntilUsingZuluTime(): void
    {
        $string = 'FREQ=MONTHLY;UNTIL=20170331T040000Z;INTERVAL=1;WKST=MO';

        $this->rule->loadFromString($string);

        $this->assertSame($string, $this->rule->getString(Rule::TZ_FIXED));
    }

    public function testGetStringWithoutExplicitWkst(): void
    {
        $string = 'FREQ=MONTHLY;COUNT=2;INTERVAL=1';

        $this->rule->loadFromString($string);

        $this->assertStringNotContainsString('WKST', $this->rule->getString());
    }

    public function testGetStringWithExplicitWkst(): void
    {
        $string = 'FREQ=MONTHLY;COUNT=2;INTERVAL=1;WKST=TH';

        $this->rule->loadFromString($string);

        $this->assertStringContainsString('WKST=TH', $this->rule->getString());
    }

    public function testSetStartDateAffectsStringOutput(): void
    {
        $this->rule->loadFromString('FREQ=MONTHLY;COUNT=2');
        $this->assertEquals('FREQ=MONTHLY;COUNT=2', $this->rule->getString());

        $this->rule->setStartDate(new \DateTime('2015-12-10'));
        $this->assertEquals('FREQ=MONTHLY;COUNT=2', $this->rule->getString());

        $this->rule->setStartDate(new \DateTime('2015-12-10'), true);
        $this->assertEquals('FREQ=MONTHLY;COUNT=2;DTSTART=20151210T000000', $this->rule->getString());

        $this->rule->setStartDate(new \DateTime('2015-12-10'), false);
        $this->assertEquals('FREQ=MONTHLY;COUNT=2', $this->rule->getString());
    }

    public function testBadInterval(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->rule->setInterval(0);
    }

    public function testEmptyByDayThrowsException(): void
    {
        $this->expectException(InvalidRRule::class);
        $this->rule->setByDay([]);
    }

    public function testEmptyByDayFromStringThrowsException(): void
    {
        $this->expectException(InvalidRRule::class);
        $this->rule->loadFromString('FREQ=WEEKLY;BYDAY=;INTERVAL=1;UNTIL=20160725');
    }

    public function testBadWeekStart(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->rule->setWeekStart('monday');
    }

    #[DataProvider('exampleRruleProvider')]
    public function testRepeatsIndefinitely(string $string, bool $expected): void
    {
        $this->assertSame($expected, $this->rule->loadFromString($string)->repeatsIndefinitely());
    }

    public function testCreateFromTextSimple(): void
    {
        $rule = Rule::createFromText('every day');
        $this->assertEquals('DAILY', $rule->getFreqAsText());
    }

    public function testCreateFromTextWithInterval(): void
    {
        $rule = Rule::createFromText('every 2 weeks');
        $this->assertEquals('WEEKLY', $rule->getFreqAsText());
        $this->assertEquals(2, $rule->getInterval());
    }

    public function testCreateFromTextWithCount(): void
    {
        $rule = Rule::createFromText('every day for 3 times');
        $this->assertEquals('DAILY', $rule->getFreqAsText());
        $this->assertEquals(3, $rule->getCount());
    }

    public function testCreateFromTextWithWeekday(): void
    {
        $rule = Rule::createFromText('every monday');
        $this->assertEquals('WEEKLY', $rule->getFreqAsText());
        $this->assertEquals(['MO'], $rule->getByDay());
    }

    public function testCreateFromTextWithMonth(): void
    {
        $rule = Rule::createFromText('every january');
        $this->assertEquals('YEARLY', $rule->getFreqAsText());
        $this->assertEquals([1], $rule->getByMonth());
    }

    public function testCreateFromTextWeekdays(): void
    {
        $rule = Rule::createFromText('every weekday');
        $this->assertEquals('WEEKLY', $rule->getFreqAsText());
        $this->assertEquals(['MO', 'TU', 'WE', 'TH', 'FR'], $rule->getByDay());
    }

    public function testCreateFromTextWithStartDate(): void
    {
        $startDate = new \DateTime('2025-01-01');
        $rule = Rule::createFromText('every day', $startDate);
        $this->assertEquals('DAILY', $rule->getFreqAsText());
        $this->assertEquals($startDate, $rule->getStartDate());
    }

    public function testCreateFromTextWithEndDate(): void
    {
        $startDate = new \DateTime('2025-01-01');
        $endDate = new \DateTime('2025-12-31');
        $rule = Rule::createFromText('every day', $startDate, $endDate);
        $this->assertEquals('DAILY', $rule->getFreqAsText());
        $this->assertEquals($endDate, $rule->getEndDate());
    }

    public function testCreateFromTextWithTimezone(): void
    {
        $rule = Rule::createFromText('every day', null, null, 'America/New_York');
        $this->assertEquals('DAILY', $rule->getFreqAsText());
        $this->assertEquals('America/New_York', $rule->getTimezone());
    }

    public function testCreateFromTextInvalid(): void
    {
        $this->expectException(InvalidRRule::class);
        Rule::createFromText('this is not a valid recurrence text');
    }

    public function testCreateFromTextEmpty(): void
    {
        $this->expectException(InvalidRRule::class);
        Rule::createFromText('');
    }

    #[DataProvider('validTextProvider')]
    public function testCreateFromTextValid(string $text, string $expectedFreq, ?int $expectedInterval, ?int $expectedCount): void
    {
        $rule = Rule::createFromText($text);
        $this->assertEquals($expectedFreq, $rule->getFreqAsText());
        
        if ($expectedInterval !== null) {
            $this->assertEquals($expectedInterval, $rule->getInterval());
        }
        
        if ($expectedCount !== null) {
            $this->assertEquals($expectedCount, $rule->getCount());
        }
    }

    public static function validTextProvider(): array
    {
        return [
            ['every day', 'DAILY', null, null],
            ['every 3 days', 'DAILY', 3, null],
            ['every week', 'WEEKLY', null, null],
            ['every 2 weeks', 'WEEKLY', 2, null],
            ['every month', 'MONTHLY', null, null],
            ['every 6 months', 'MONTHLY', 6, null],
            ['every year', 'YEARLY', null, null],
            ['every 2 years', 'YEARLY', 2, null],
            ['every hour', 'HOURLY', null, null],
            ['every 4 hours', 'HOURLY', 4, null],
            ['every minute', 'MINUTELY', null, null],
            ['every 15 minutes', 'MINUTELY', 15, null],
            ['every day for 5 times', 'DAILY', null, 5],
            ['every 2 weeks for 10 times', 'WEEKLY', 2, 10],
        ];
    }

    /**
     * Taken from https://tools.ietf.org/html/rfc5545#section-3.8.5.3
     */
    public static function exampleRruleProvider(): array
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

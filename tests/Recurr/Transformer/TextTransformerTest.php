<?php

namespace Tests\Recurr\Transformer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Recurr\Rule;
use Recurr\Transformer\TextTransformer;
use Recurr\Transformer\Translator;
use Symfony\Component\Yaml\Yaml;

class TextTransformerTest extends TestCase
{
    private static array $languages = [];

    #[DataProvider('generateTests')]
    public function testFormatting(string $lang, string $rule, string $expected): void
    {
        // Sunday, March 16th is our reference start date
        $dateTime = new \DateTime('2014-03-16 04:00:00');
        $rule = new Rule($rule, $dateTime);

        $transformer = new TextTransformer(new Translator($lang));
        $this->assertEquals(self::$languages[$lang][$expected], $transformer->transform($rule));
    }

    public static function generateTests(): array
    {
        $baseTests = [
            // Count
            [
                'FREQ=YEARLY;COUNT=1',
                'yearly on March 16 for 1 time',
            ],
            // CountPlural
            [
                'FREQ=YEARLY;COUNT=3',
                'yearly on March 16 for 3 times',
            ],
            // Until
            [
                'FREQ=YEARLY;UNTIL=20140704T040000Z',
                'yearly on March 16 until July 4, 2014',
            ],
            // FullyConvertible
            [
                'FREQ=YEARLY;BYHOUR=1',
                'yearly on March 16 (~ approximate)',
            ],
            [
                'FREQ=YEARLY;BYMINUTE=1',
                'yearly on March 16 (~ approximate)',
            ],
            [
                'FREQ=YEARLY;BYSECOND=1',
                'yearly on March 16 (~ approximate)',
            ],
            [
                'FREQ=MONTHLY;BYWEEKNO=50',
                'monthly (~ approximate)',
            ],
            [
                'FREQ=MONTHLY;BYYEARDAY=200',
                'monthly (~ approximate)',
            ],

            // Monthly
            [
                'FREQ=MONTHLY',
                'monthly',
            ],
            // MonthlyPlural
            [
                'FREQ=MONTHLY;INTERVAL=10',
                'every 10 months',
            ],
            // MonthlyByMonth
            [
                'FREQ=MONTHLY;BYMONTH=1,8,5',
                'every January, May and August',
            ],
            [
                'FREQ=MONTHLY;INTERVAL=2;BYMONTH=1,8,5',
                'every 2 months in January, May and August',
            ],
            // MonthlyByMonthDay
            [
                'FREQ=MONTHLY;BYMONTHDAY=5,1,21',
                'monthly on the 1st, 5th and 21st',
            ],
            [
                'FREQ=MONTHLY;BYMONTHDAY=5,1,21;BYDAY=TU,FR',
                'monthly on Tuesday or Friday the 1st, 5th or 21st',
            ],
            // MonthlyByNegativeMonthDay
            [
                'FREQ=MONTHLY;BYMONTHDAY=-1,21',
                'monthly on the 21st day and on the last day',
            ],
            [
                'FREQ=MONTHLY;BYMONTHDAY=-2,1;BYDAY=TU,FR',
                'monthly on Tuesday or Friday the 1st day or 2nd to the last day',
            ],
            // MonthlyByDay
            [
                'FREQ=MONTHLY;BYDAY=TU,WE,FR',
                'monthly on Tuesday, Wednesday and Friday',
            ],
            [
                'FREQ=MONTHLY;BYDAY=+4MO',
                'monthly on the 4th Monday',
            ],
            [
                'FREQ=MONTHLY;BYDAY=+4MO,+2TU',
                'monthly on the 4th Monday and 2nd Tuesday',
            ],
            [
                'FREQ=MONTHLY;BYDAY=+4MO,+2TU,+3WE',
                'monthly on the 4th Monday, 2nd Tuesday and 3rd Wednesday',
            ],
            [
                'FREQ=MONTHLY;BYDAY=1MO,+1TU,+2WE,+3WE,+4WE,-1TH,-2FR,-3SA,-4SU',
                'monthly on the 1st Monday, 1st Tuesday, 2nd Wednesday, 3rd Wednesday, 4th Wednesday, last Thursday, 2nd to the last Friday, 3rd to the last Saturday and 4th to the last Sunday',
            ],

            // Daily
            [
                'FREQ=DAILY',
                'daily',
            ],
            // DailyPlural
            [
                'FREQ=DAILY;INTERVAL=10',
                'every 10 days',
            ],
            // DailyByMonth
            [
                'FREQ=DAILY;BYMONTH=1,8,5',
                'daily in January, May and August',
            ],
            [
                'FREQ=DAILY;INTERVAL=2;BYMONTH=1,8,5',
                'every 2 days in January, May and August',
            ],
            // DailyByMonthDay
            [
                'FREQ=DAILY;BYMONTHDAY=5,1,21',
                'daily on the 1st, 5th and 21st of the month',
            ],
            [
                'FREQ=DAILY;BYMONTHDAY=5,1,21;BYDAY=TU,FR',
                'daily on Tuesday or Friday the 1st, 5th or 21st of the month',
            ],
            // DailyByDay
            [
                'FREQ=DAILY;BYDAY=TU,WE,FR',
                'daily on Tuesday, Wednesday and Friday',
            ],

            // Yearly
            [
                'FREQ=YEARLY',
                'yearly on March 16',
            ],
            // YearlyPlural
            [
                'FREQ=YEARLY;INTERVAL=10',
                'every 10 years on March 16',
            ],
            // YearlyByMonth
            [
                'FREQ=YEARLY;BYMONTH=1,8,5',
                'every January, May and August',
            ],
            [
                'FREQ=YEARLY;INTERVAL=2;BYMONTH=1,8,5',
                'every 2 years in January, May and August',
            ],
            // ComplexYearly (first Tuesday that comes after a Monday in November, every 4years)
            [
                'FREQ=YEARLY;INTERVAL=4;BYMONTH=11;BYDAY=TU;BYMONTHDAY=2,3,4,5,6,7,8',
                'every 4 years in November on Tuesday the 2nd, 3rd, 4th, 5th, 6th, 7th or 8th of the month',
            ],
            // YearlyByMonthDay
            [
                'FREQ=YEARLY;BYMONTHDAY=5,1,21',
                'yearly on the 1st, 5th and 21st of the month',
            ],
            [
                'FREQ=YEARLY;BYMONTHDAY=5,1,21;BYDAY=TU,FR',
                'yearly on Tuesday or Friday the 1st, 5th or 21st of the month',
            ],
            // YearlyByDay
            [
                'FREQ=YEARLY;BYDAY=TU,WE,FR',
                'yearly on Tuesday, Wednesday and Friday',
            ],
            // YearlyByYearDay
            [
                'FREQ=YEARLY;BYYEARDAY=1,200',
                'yearly on the 1st and 200th day',
            ],
            // YearlyByWeekNumber
            [
                'FREQ=YEARLY;BYWEEKNO=3',
                'yearly in week 3 on Sunday',
            ],
            [
                'FREQ=YEARLY;BYWEEKNO=3,30,20',
                'yearly in weeks 3, 20 and 30 on Sunday',
            ],

            // Weekly
            [
                'FREQ=WEEKLY',
                'weekly on Sunday',
            ],
            // WeeklyPlural
            [
                'FREQ=WEEKLY;INTERVAL=10',
                'every 10 weeks on Sunday',
            ],
            // WeeklyByMonth
            [
                'FREQ=WEEKLY;BYMONTH=1,8,5',
                'weekly on Sunday in January, May and August',
            ],
            [
                'FREQ=WEEKLY;INTERVAL=2;BYMONTH=1,8,5',
                'every 2 weeks on Sunday in January, May and August',
            ],
            // WeeklyByMonthDay
            [
                'FREQ=WEEKLY;BYMONTHDAY=5,1,21',
                'weekly on the 1st, 5th and 21st of the month',
            ],
            [
                'FREQ=WEEKLY;BYMONTHDAY=5,1,21;BYDAY=TU,FR',
                'weekly on Tuesday or Friday the 1st, 5th or 21st of the month',
            ],
            // WeeklyByDay
            [
                'FREQ=WEEKLY;BYDAY=TU,WE,FR',
                'weekly on Tuesday, Wednesday and Friday',
            ],

            // Check that start date impacts wording for yearly
            [
                'FREQ=YEARLY;BYMONTH=3',
                'yearly on March 16',
            ],
            [
                'FREQ=YEARLY;INTERVAL=2;BYMONTH=3',
                'every 2 years on March 16',
            ],
            [
                'FREQ=YEARLY;BYMONTH=3;COUNT=5',
                'yearly on March 16 for 5 times',
            ],
            [
                'FREQ=YEARLY;BYMONTH=3;UNTIL=20121231T235959Z',
                'yearly on March 16 until December 31, 2012',
            ],

            // Hourly
            [
                'FREQ=HOURLY',
                'hourly',
            ],
            // HourlyPlural
            [
                'FREQ=HOURLY;INTERVAL=10',
                'every 10 hours',
            ],
            // HourlyByMonth
            [
                'FREQ=HOURLY;BYMONTH=1,8,5',
                'hourly in January, May and August',
            ],
            [
                'FREQ=HOURLY;INTERVAL=2;BYMONTH=1,8,5',
                'every 2 hours in January, May and August',
            ],
            // HourlyByMonthDay
            [
                'FREQ=HOURLY;BYMONTHDAY=5,1,21',
                'hourly on the 1st, 5th and 21st of the month',
            ],
            [
                'FREQ=HOURLY;BYMONTHDAY=5,1,21;BYDAY=TU,FR',
                'hourly on Tuesday or Friday the 1st, 5th or 21st of the month',
            ],
            // HourlyByDay
            [
                'FREQ=HOURLY;BYDAY=TU,WE,FR',
                'hourly on Tuesday, Wednesday and Friday',
            ],
        ];

        $tests = [];
        foreach (glob(__DIR__.'/Translations/*.yml') ?: [] as $file) {
            $lang = basename($file, '.yml');
            self::$languages[$lang] = Yaml::parse(file_get_contents($file) ?: '');
            $tests = array_merge($tests, array_map(function (array $test) use ($lang): array {
                array_unshift($test, $lang);

                return $test;
            }, $baseTests));
        }

        return $tests;
    }
}

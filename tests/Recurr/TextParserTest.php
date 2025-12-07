<?php

namespace Tests\Recurr;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Recurr\TextParser;

class TextParserTest extends TestCase
{
    protected TextParser $parser;

    public function setUp(): void
    {
        $this->parser = new TextParser();
    }

    public function testParseTextSimpleDaily(): void
    {
        $result = $this->parser->parseText('every day');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('FREQ', $result);
        $this->assertEquals('DAILY', $result['FREQ']);
    }

    public function testParseTextSimpleWeekly(): void
    {
        $result = $this->parser->parseText('every week');
        
        $this->assertIsArray($result);
        $this->assertEquals('WEEKLY', $result['FREQ']);
    }

    public function testParseTextSimpleMonthly(): void
    {
        $result = $this->parser->parseText('every month');
        
        $this->assertIsArray($result);
        $this->assertEquals('MONTHLY', $result['FREQ']);
    }

    public function testParseTextSimpleYearly(): void
    {
        $result = $this->parser->parseText('every year');
        
        $this->assertIsArray($result);
        $this->assertEquals('YEARLY', $result['FREQ']);
    }

    public function testParseTextSimpleHourly(): void
    {
        $result = $this->parser->parseText('every hour');
        
        $this->assertIsArray($result);
        $this->assertEquals('HOURLY', $result['FREQ']);
    }

    public function testParseTextSimpleMinutely(): void
    {
        $result = $this->parser->parseText('every minute');
        
        $this->assertIsArray($result);
        $this->assertEquals('MINUTELY', $result['FREQ']);
    }

    public function testParseTextWithInterval(): void
    {
        $result = $this->parser->parseText('every 2 days');
        
        $this->assertIsArray($result);
        $this->assertEquals('DAILY', $result['FREQ']);
        $this->assertArrayHasKey('INTERVAL', $result);
        $this->assertEquals('2', $result['INTERVAL']);
    }

    public function testParseTextWithLargeInterval(): void
    {
        $result = $this->parser->parseText('every 10 weeks');
        
        $this->assertIsArray($result);
        $this->assertEquals('WEEKLY', $result['FREQ']);
        $this->assertEquals('10', $result['INTERVAL']);
    }

    public function testParseTextWithCount(): void
    {
        $result = $this->parser->parseText('every day for 3 times');
        
        $this->assertIsArray($result);
        $this->assertEquals('DAILY', $result['FREQ']);
        $this->assertArrayHasKey('COUNT', $result);
        $this->assertEquals('3', $result['COUNT']);
    }

    public function testParseTextWithCountNoTimesKeyword(): void
    {
        $result = $this->parser->parseText('every day for 5');
        
        $this->assertIsArray($result);
        $this->assertEquals('DAILY', $result['FREQ']);
        $this->assertEquals('5', $result['COUNT']);
    }

    public function testParseTextWithIntervalAndCount(): void
    {
        $result = $this->parser->parseText('every 2 weeks for 10 times');
        
        $this->assertIsArray($result);
        $this->assertEquals('WEEKLY', $result['FREQ']);
        $this->assertEquals('2', $result['INTERVAL']);
        $this->assertEquals('10', $result['COUNT']);
    }

    public function testParseTextMonday(): void
    {
        $result = $this->parser->parseText('every monday');
        
        $this->assertIsArray($result);
        $this->assertEquals('WEEKLY', $result['FREQ']);
        $this->assertArrayHasKey('BYDAY', $result);
        $this->assertEquals('MO', $result['BYDAY']);
    }

    public function testParseTextTuesday(): void
    {
        $result = $this->parser->parseText('every tuesday');
        
        $this->assertIsArray($result);
        $this->assertEquals('WEEKLY', $result['FREQ']);
        $this->assertEquals('TU', $result['BYDAY']);
    }

    public function testParseTextWednesday(): void
    {
        $result = $this->parser->parseText('every wednesday');
        
        $this->assertIsArray($result);
        $this->assertEquals('WEEKLY', $result['FREQ']);
        $this->assertEquals('WE', $result['BYDAY']);
    }

    public function testParseTextThursday(): void
    {
        $result = $this->parser->parseText('every thursday');
        
        $this->assertIsArray($result);
        $this->assertEquals('WEEKLY', $result['FREQ']);
        $this->assertEquals('TH', $result['BYDAY']);
    }

    public function testParseTextFriday(): void
    {
        $result = $this->parser->parseText('every friday');
        
        $this->assertIsArray($result);
        $this->assertEquals('WEEKLY', $result['FREQ']);
        $this->assertEquals('FR', $result['BYDAY']);
    }

    public function testParseTextSaturday(): void
    {
        $result = $this->parser->parseText('every saturday');
        
        $this->assertIsArray($result);
        $this->assertEquals('WEEKLY', $result['FREQ']);
        $this->assertEquals('SA', $result['BYDAY']);
    }

    public function testParseTextSunday(): void
    {
        $result = $this->parser->parseText('every sunday');
        
        $this->assertIsArray($result);
        $this->assertEquals('WEEKLY', $result['FREQ']);
        $this->assertEquals('SU', $result['BYDAY']);
    }

    public function testParseTextWeekdays(): void
    {
        $result = $this->parser->parseText('every weekday');
        
        $this->assertIsArray($result);
        $this->assertEquals('WEEKLY', $result['FREQ']);
        $this->assertArrayHasKey('BYDAY', $result);
        $this->assertEquals('MO,TU,WE,TH,FR', $result['BYDAY']);
    }

    public function testParseTextJanuary(): void
    {
        $result = $this->parser->parseText('every january');
        
        $this->assertIsArray($result);
        $this->assertEquals('YEARLY', $result['FREQ']);
        $this->assertArrayHasKey('BYMONTH', $result);
        $this->assertEquals('1', $result['BYMONTH']);
    }

    public function testParseTextFebruary(): void
    {
        $result = $this->parser->parseText('every february');
        
        $this->assertIsArray($result);
        $this->assertEquals('YEARLY', $result['FREQ']);
        $this->assertEquals('2', $result['BYMONTH']);
    }

    public function testParseTextMarch(): void
    {
        $result = $this->parser->parseText('every march');
        
        $this->assertIsArray($result);
        $this->assertEquals('YEARLY', $result['FREQ']);
        $this->assertEquals('3', $result['BYMONTH']);
    }

    public function testParseTextApril(): void
    {
        $result = $this->parser->parseText('every april');
        
        $this->assertIsArray($result);
        $this->assertEquals('YEARLY', $result['FREQ']);
        $this->assertEquals('4', $result['BYMONTH']);
    }

    public function testParseTextMay(): void
    {
        $result = $this->parser->parseText('every may');
        
        $this->assertIsArray($result);
        $this->assertEquals('YEARLY', $result['FREQ']);
        $this->assertEquals('5', $result['BYMONTH']);
    }

    public function testParseTextJune(): void
    {
        $result = $this->parser->parseText('every june');
        
        $this->assertIsArray($result);
        $this->assertEquals('YEARLY', $result['FREQ']);
        $this->assertEquals('6', $result['BYMONTH']);
    }

    public function testParseTextJuly(): void
    {
        $result = $this->parser->parseText('every july');
        
        $this->assertIsArray($result);
        $this->assertEquals('YEARLY', $result['FREQ']);
        $this->assertEquals('7', $result['BYMONTH']);
    }

    public function testParseTextAugust(): void
    {
        $result = $this->parser->parseText('every august');
        
        $this->assertIsArray($result);
        $this->assertEquals('YEARLY', $result['FREQ']);
        $this->assertEquals('8', $result['BYMONTH']);
    }

    public function testParseTextSeptember(): void
    {
        $result = $this->parser->parseText('every september');
        
        $this->assertIsArray($result);
        $this->assertEquals('YEARLY', $result['FREQ']);
        $this->assertEquals('9', $result['BYMONTH']);
    }

    public function testParseTextOctober(): void
    {
        $result = $this->parser->parseText('every october');
        
        $this->assertIsArray($result);
        $this->assertEquals('YEARLY', $result['FREQ']);
        $this->assertEquals('10', $result['BYMONTH']);
    }

    public function testParseTextNovember(): void
    {
        $result = $this->parser->parseText('every november');
        
        $this->assertIsArray($result);
        $this->assertEquals('YEARLY', $result['FREQ']);
        $this->assertEquals('11', $result['BYMONTH']);
    }

    public function testParseTextDecember(): void
    {
        $result = $this->parser->parseText('every december');
        
        $this->assertIsArray($result);
        $this->assertEquals('YEARLY', $result['FREQ']);
        $this->assertEquals('12', $result['BYMONTH']);
    }

    public function testParseTextCaseInsensitive(): void
    {
        $result1 = $this->parser->parseText('EVERY DAY');
        $result2 = $this->parser->parseText('Every Day');
        $result3 = $this->parser->parseText('every day');
        
        $this->assertEquals($result1, $result2);
        $this->assertEquals($result2, $result3);
    }

    public function testParseTextWithExtraWhitespace(): void
    {
        $result = $this->parser->parseText('  every   day  ');
        
        $this->assertIsArray($result);
        $this->assertEquals('DAILY', $result['FREQ']);
    }

    public function testParseTextInvalidReturnsNull(): void
    {
        $result = $this->parser->parseText('this is not valid');
        
        $this->assertNull($result);
    }

    public function testParseTextEmptyReturnsNull(): void
    {
        $result = $this->parser->parseText('');
        
        $this->assertNull($result);
    }

    public function testParseTextOnlyWhitespaceReturnsNull(): void
    {
        $result = $this->parser->parseText('   ');
        
        $this->assertNull($result);
    }

    public function testParseTextWithoutEveryReturnsNull(): void
    {
        $result = $this->parser->parseText('day');
        
        $this->assertNull($result);
    }

    public function testParseTextIncompleteReturnsNull(): void
    {
        $result = $this->parser->parseText('every');
        
        $this->assertNull($result);
    }

    #[DataProvider('validTextProvider')]
    public function testParseTextValid(string $text, string $expectedFreq, ?string $expectedInterval, ?string $expectedCount, ?string $expectedByDay, ?string $expectedByMonth): void
    {
        $result = $this->parser->parseText($text);
        
        $this->assertIsArray($result);
        $this->assertEquals($expectedFreq, $result['FREQ']);
        
        if ($expectedInterval !== null) {
            $this->assertArrayHasKey('INTERVAL', $result);
            $this->assertEquals($expectedInterval, $result['INTERVAL']);
        }
        
        if ($expectedCount !== null) {
            $this->assertArrayHasKey('COUNT', $result);
            $this->assertEquals($expectedCount, $result['COUNT']);
        }
        
        if ($expectedByDay !== null) {
            $this->assertArrayHasKey('BYDAY', $result);
            $this->assertEquals($expectedByDay, $result['BYDAY']);
        }
        
        if ($expectedByMonth !== null) {
            $this->assertArrayHasKey('BYMONTH', $result);
            $this->assertEquals($expectedByMonth, $result['BYMONTH']);
        }
    }

    public static function validTextProvider(): array
    {
        return [
            ['every day', 'DAILY', null, null, null, null],
            ['every 2 days', 'DAILY', '2', null, null, null],
            ['every 5 days', 'DAILY', '5', null, null, null],
            ['every week', 'WEEKLY', null, null, null, null],
            ['every 3 weeks', 'WEEKLY', '3', null, null, null],
            ['every month', 'MONTHLY', null, null, null, null],
            ['every 6 months', 'MONTHLY', '6', null, null, null],
            ['every year', 'YEARLY', null, null, null, null],
            ['every 2 years', 'YEARLY', '2', null, null, null],
            ['every hour', 'HOURLY', null, null, null, null],
            ['every 4 hours', 'HOURLY', '4', null, null, null],
            ['every minute', 'MINUTELY', null, null, null, null],
            ['every 15 minutes', 'MINUTELY', '15', null, null, null],
            ['every day for 5 times', 'DAILY', null, '5', null, null],
            ['every 2 weeks for 10 times', 'WEEKLY', '2', '10', null, null],
            ['every monday', 'WEEKLY', null, null, 'MO', null],
            ['every tuesday', 'WEEKLY', null, null, 'TU', null],
            ['every wednesday', 'WEEKLY', null, null, 'WE', null],
            ['every thursday', 'WEEKLY', null, null, 'TH', null],
            ['every friday', 'WEEKLY', null, null, 'FR', null],
            ['every saturday', 'WEEKLY', null, null, 'SA', null],
            ['every sunday', 'WEEKLY', null, null, 'SU', null],
            ['every weekday', 'WEEKLY', null, null, 'MO,TU,WE,TH,FR', null],
            ['every january', 'YEARLY', null, null, null, '1'],
            ['every february', 'YEARLY', null, null, null, '2'],
            ['every march', 'YEARLY', null, null, null, '3'],
            ['every april', 'YEARLY', null, null, null, '4'],
            ['every may', 'YEARLY', null, null, null, '5'],
            ['every june', 'YEARLY', null, null, null, '6'],
            ['every july', 'YEARLY', null, null, null, '7'],
            ['every august', 'YEARLY', null, null, null, '8'],
            ['every september', 'YEARLY', null, null, null, '9'],
            ['every october', 'YEARLY', null, null, null, '10'],
            ['every november', 'YEARLY', null, null, null, '11'],
            ['every december', 'YEARLY', null, null, null, '12'],
        ];
    }

    #[DataProvider('invalidTextProvider')]
    public function testParseTextInvalid(string $text): void
    {
        $result = $this->parser->parseText($text);
        
        $this->assertNull($result);
    }

    public static function invalidTextProvider(): array
    {
        return [
            [''],
            ['   '],
            ['every'],
            ['day'],
            ['week'],
            ['this is invalid'],
            ['random text'],
            ['every invalid'],
            ['every 0 days'],
            ['every -1 weeks'],
            ['123 every day'],
        ];
    }

    public function testParseTextShortDayNames(): void
    {
        $resultMo = $this->parser->parseText('every mo');
        $this->assertIsArray($resultMo);
        $this->assertEquals('MO', $resultMo['BYDAY']);
        
        $resultTu = $this->parser->parseText('every tu');
        $this->assertIsArray($resultTu);
        $this->assertEquals('TU', $resultTu['BYDAY']);
        
        $resultWe = $this->parser->parseText('every we');
        $this->assertIsArray($resultWe);
        $this->assertEquals('WE', $resultWe['BYDAY']);
        
        $resultTh = $this->parser->parseText('every th');
        $this->assertIsArray($resultTh);
        $this->assertEquals('TH', $resultTh['BYDAY']);
        
        $resultFr = $this->parser->parseText('every fr');
        $this->assertIsArray($resultFr);
        $this->assertEquals('FR', $resultFr['BYDAY']);
        
        $resultSa = $this->parser->parseText('every sa');
        $this->assertIsArray($resultSa);
        $this->assertEquals('SA', $resultSa['BYDAY']);
        
        $resultSu = $this->parser->parseText('every su');
        $this->assertIsArray($resultSu);
        $this->assertEquals('SU', $resultSu['BYDAY']);
    }

    public function testParseTextShortMonthNames(): void
    {
        $resultJan = $this->parser->parseText('every jan');
        $this->assertIsArray($resultJan);
        $this->assertEquals('1', $resultJan['BYMONTH']);
        
        $resultFeb = $this->parser->parseText('every feb');
        $this->assertIsArray($resultFeb);
        $this->assertEquals('2', $resultFeb['BYMONTH']);
        
        $resultMar = $this->parser->parseText('every mar');
        $this->assertIsArray($resultMar);
        $this->assertEquals('3', $resultMar['BYMONTH']);
        
        $resultApr = $this->parser->parseText('every apr');
        $this->assertIsArray($resultApr);
        $this->assertEquals('4', $resultApr['BYMONTH']);
        
        $resultJun = $this->parser->parseText('every jun');
        $this->assertIsArray($resultJun);
        $this->assertEquals('6', $resultJun['BYMONTH']);
        
        $resultJul = $this->parser->parseText('every jul');
        $this->assertIsArray($resultJul);
        $this->assertEquals('7', $resultJul['BYMONTH']);
        
        $resultAug = $this->parser->parseText('every aug');
        $this->assertIsArray($resultAug);
        $this->assertEquals('8', $resultAug['BYMONTH']);
        
        $resultSep = $this->parser->parseText('every sep');
        $this->assertIsArray($resultSep);
        $this->assertEquals('9', $resultSep['BYMONTH']);
        
        $resultOct = $this->parser->parseText('every oct');
        $this->assertIsArray($resultOct);
        $this->assertEquals('10', $resultOct['BYMONTH']);
        
        $resultNov = $this->parser->parseText('every nov');
        $this->assertIsArray($resultNov);
        $this->assertEquals('11', $resultNov['BYMONTH']);
        
        $resultDec = $this->parser->parseText('every dec');
        $this->assertIsArray($resultDec);
        $this->assertEquals('12', $resultDec['BYMONTH']);
    }

    public function testParseTextPluralForms(): void
    {
        $daysSingular = $this->parser->parseText('every day');
        $daysPlural = $this->parser->parseText('every 3 days');
        $this->assertIsArray($daysSingular);
        $this->assertIsArray($daysPlural);
        $this->assertEquals('DAILY', $daysSingular['FREQ']);
        $this->assertEquals('DAILY', $daysPlural['FREQ']);
        
        $weeksSingular = $this->parser->parseText('every week');
        $weeksPlural = $this->parser->parseText('every 2 weeks');
        $this->assertIsArray($weeksSingular);
        $this->assertIsArray($weeksPlural);
        $this->assertEquals('WEEKLY', $weeksSingular['FREQ']);
        $this->assertEquals('WEEKLY', $weeksPlural['FREQ']);
        
        $monthsSingular = $this->parser->parseText('every month');
        $monthsPlural = $this->parser->parseText('every 2 months');
        $this->assertIsArray($monthsSingular);
        $this->assertIsArray($monthsPlural);
        $this->assertEquals('MONTHLY', $monthsSingular['FREQ']);
        $this->assertEquals('MONTHLY', $monthsPlural['FREQ']);
        
        $yearsSingular = $this->parser->parseText('every year');
        $yearsPlural = $this->parser->parseText('every 2 years');
        $this->assertIsArray($yearsSingular);
        $this->assertIsArray($yearsPlural);
        $this->assertEquals('YEARLY', $yearsSingular['FREQ']);
        $this->assertEquals('YEARLY', $yearsPlural['FREQ']);
        
        $hoursSingular = $this->parser->parseText('every hour');
        $hoursPlural = $this->parser->parseText('every 2 hours');
        $this->assertIsArray($hoursSingular);
        $this->assertIsArray($hoursPlural);
        $this->assertEquals('HOURLY', $hoursSingular['FREQ']);
        $this->assertEquals('HOURLY', $hoursPlural['FREQ']);
        
        $minutesSingular = $this->parser->parseText('every minute');
        $minutesPlural = $this->parser->parseText('every 2 minutes');
        $this->assertIsArray($minutesSingular);
        $this->assertIsArray($minutesPlural);
        $this->assertEquals('MINUTELY', $minutesSingular['FREQ']);
        $this->assertEquals('MINUTELY', $minutesPlural['FREQ']);
    }

    public function testParseTextTimesSingularAndPlural(): void
    {
        $singular = $this->parser->parseText('every day for 1 time');
        $this->assertIsArray($singular);
        $this->assertEquals('1', $singular['COUNT']);
        
        $plural = $this->parser->parseText('every day for 5 times');
        $this->assertIsArray($plural);
        $this->assertEquals('5', $plural['COUNT']);
    }

    public function testParseTextWeekdaySingularAndPlural(): void
    {
        $singular = $this->parser->parseText('every weekday');
        $this->assertIsArray($singular);
        $this->assertEquals('MO,TU,WE,TH,FR', $singular['BYDAY']);
        
        $plural = $this->parser->parseText('every weekdays');
        $this->assertIsArray($plural);
        $this->assertEquals('MO,TU,WE,TH,FR', $plural['BYDAY']);
    }
}

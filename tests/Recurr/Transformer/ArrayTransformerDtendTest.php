<?php

namespace Tests\Recurr\Transformer;

use Recurr\Rule;
use Recurr\Transformer\ArrayTransformerConfig;

class ArrayTransformerDtendTest extends ArrayTransformerBase
{
    public function testDtend(): void
    {
        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=3;DTEND=20140316T040000',
            new \DateTime('2014-03-14 04:00:00')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(3, $computed);
        $this->assertEquals(new \DateTime('2014-03-14 04:00:00'), $computed[0]?->getStart());
        $this->assertEquals(new \DateTime('2014-03-16 04:00:00'), $computed[0]?->getEnd());
        $this->assertEquals(new \DateTime('2014-04-14 04:00:00'), $computed[1]?->getStart());
        $this->assertEquals(new \DateTime('2014-04-16 04:00:00'), $computed[1]?->getEnd());
        $this->assertEquals(new \DateTime('2014-05-14 04:00:00'), $computed[2]?->getStart());
        $this->assertEquals(new \DateTime('2014-05-16 04:00:00'), $computed[2]?->getEnd());
    }

    public function testDtendWithoutLastDayOfMonthFix(): void
    {
        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=5;DTEND=20140201T040000',
            new \DateTime('2014-01-31 04:00:00')
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new \DateTime('2014-01-31 04:00:00'), $computed[0]?->getStart());
        $this->assertEquals(new \DateTime('2014-02-01 04:00:00'), $computed[0]?->getEnd());
        $this->assertEquals(new \DateTime('2014-03-31 04:00:00'), $computed[1]?->getStart());
        $this->assertEquals(new \DateTime('2014-04-01 04:00:00'), $computed[1]?->getEnd());
        $this->assertEquals(new \DateTime('2014-05-31 04:00:00'), $computed[2]?->getStart());
        $this->assertEquals(new \DateTime('2014-06-01 04:00:00'), $computed[2]?->getEnd());
        $this->assertEquals(new \DateTime('2014-07-31 04:00:00'), $computed[3]?->getStart());
        $this->assertEquals(new \DateTime('2014-08-01 04:00:00'), $computed[3]?->getEnd());
        $this->assertEquals(new \DateTime('2014-08-31 04:00:00'), $computed[4]?->getStart());
        $this->assertEquals(new \DateTime('2014-09-01 04:00:00'), $computed[4]?->getEnd());
    }

    public function testDtendWithLastDayOfMonthFix(): void
    {
        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=5;DTEND=20140201T040000',
            new \DateTime('2014-01-31 04:00:00')
        );

        $config = new ArrayTransformerConfig();
        $config->enableLastDayOfMonthFix();
        $this->transformer->setConfig($config);

        $computed = $this->transformer->transform($rule);

        $this->assertCount(5, $computed);
        $this->assertEquals(new \DateTime('2014-01-31 04:00:00'), $computed[0]?->getStart());
        $this->assertEquals(new \DateTime('2014-02-01 04:00:00'), $computed[0]?->getEnd());
        $this->assertEquals(new \DateTime('2014-02-28 04:00:00'), $computed[1]?->getStart());
        $this->assertEquals(new \DateTime('2014-03-01 04:00:00'), $computed[1]?->getEnd());
        $this->assertEquals(new \DateTime('2014-03-31 04:00:00'), $computed[2]?->getStart());
        $this->assertEquals(new \DateTime('2014-04-01 04:00:00'), $computed[2]?->getEnd());
        $this->assertEquals(new \DateTime('2014-04-30 04:00:00'), $computed[3]?->getStart());
        $this->assertEquals(new \DateTime('2014-05-01 04:00:00'), $computed[3]?->getEnd());
        $this->assertEquals(new \DateTime('2014-05-31 04:00:00'), $computed[4]?->getStart());
        $this->assertEquals(new \DateTime('2014-06-01 04:00:00'), $computed[4]?->getEnd());
    }
    /**
     * DTEND defines an absolute end point in time, so the duration of an event
     * is the fixed number of seconds between DTSTART and DTEND. That same number
     * of seconds must be applied to every recurrence, regardless of how many days
     * or hours the intervening calendar months contain.
     *
     * Here, DTSTART=2014-01-01 and DTEND=2014-02-01 is exactly 31 days
     * (2,678,400 seconds). The February occurrence starts 2014-02-01; adding
     * 31 days lands on 2014-03-04, not 2014-03-01 (which is what a naive
     * calendar "add one month" would give).
     */
    public function testDtendDurationIsFixedSecondsNotCalendarDiff(): void
    {
        $tz = new \DateTimeZone('UTC');

        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=3;DTEND=20140201T040000',
            new \DateTime('2014-01-01 04:00:00', $tz),
            null,
            'UTC'
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(3, $computed);

        // Occurrence 0: Jan 1 → Feb 1 (31 days; the defining span)
        $this->assertEquals(new \DateTime('2014-01-01 04:00:00', $tz), $computed[0]?->getStart());
        $this->assertEquals(new \DateTime('2014-02-01 04:00:00', $tz), $computed[0]?->getEnd());

        // Occurrence 1: Feb 1 → Mar 4 (31 days = 2,678,400 s; February only has 28 days in 2014,
        // so a calendar-diff "add 1 month" would wrongly give Mar 1)
        $this->assertEquals(new \DateTime('2014-02-01 04:00:00', $tz), $computed[1]?->getStart());
        $this->assertEquals(new \DateTime('2014-03-04 04:00:00', $tz), $computed[1]?->getEnd());

        // Occurrence 2: Mar 1 → Apr 1 (31 days; March has 31 days so this agrees with calendar)
        $this->assertEquals(new \DateTime('2014-03-01 04:00:00', $tz), $computed[2]?->getStart());
        $this->assertEquals(new \DateTime('2014-04-01 04:00:00', $tz), $computed[2]?->getEnd());
    }

    /**
     * When the original event span crosses a DST clock-change, DateTime::diff()
     * records the wall-clock difference (e.g. PT25H for a span that crosses the
     * spring-forward and loses an hour). Applying that inflated interval to a
     * later occurrence that does not cross a DST boundary produces an end time
     * that is one hour too late.
     *
     * America/New_York springs forward on 2014-03-09 at 02:00 (clocks move to
     * 03:00), so the wall clock shows 25 hours between 2014-03-08 01:00 and
     * 2014-03-09 02:00, but the real elapsed time is only 24 hours (86,400 s).
     *
     * The April occurrence (2014-04-08 01:00) is fully within EDT with no DST
     * change, so adding the correct 86,400 s gives 2014-04-09 01:00, whereas
     * the calendar-diff PT25H incorrectly gives 2014-04-09 02:00.
     */
    public function testDtendDurationIsFixedSecondsAcrossDstBoundary(): void
    {
        $tz = new \DateTimeZone('America/New_York');

        // DTSTART = 2014-03-08 01:00 EST; DTEND = 2014-03-09 02:00 EDT
        // Clocks spring forward at 02:00 on Mar 9, so wall clock reads 25 h
        // but actual elapsed time is 24 h (86,400 s).
        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=2;DTEND=20140309T020000',
            new \DateTime('2014-03-08 01:00:00', $tz),
            null,
            'America/New_York'
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(2, $computed);

        // Occurrence 0: the defining span — start Mar 8 01:00, end Mar 9 02:00 EDT
        $this->assertEquals(new \DateTime('2014-03-08 01:00:00', $tz), $computed[0]?->getStart());
        $this->assertEquals(new \DateTime('2014-03-09 02:00:00', $tz), $computed[0]?->getEnd());

        // Occurrence 1: starts Apr 8 01:00 EDT (no DST change in this span).
        // Correct end = Apr 8 01:00 + 86,400 s = Apr 9 01:00 EDT.
        // Incorrect calendar-diff end (PT25H) would give Apr 9 02:00 EDT.
        $this->assertEquals(new \DateTime('2014-04-08 01:00:00', $tz), $computed[1]?->getStart());
        $this->assertEquals(new \DateTime('2014-04-09 01:00:00', $tz), $computed[1]?->getEnd());
    }
}

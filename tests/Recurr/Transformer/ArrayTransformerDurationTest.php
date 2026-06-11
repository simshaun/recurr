<?php

namespace Tests\Recurr\Transformer;

use Recurr\Exception\InvalidRRule;
use Recurr\Rule;

class ArrayTransformerDurationTest extends ArrayTransformerBase
{
    /**
     * DURATION=PT2H specifies a fixed two-hour duration. Because two hours is
     * always exactly 7,200 seconds regardless of DST or month length, this case
     * agrees with the DTEND fixed-seconds path and serves as a basic sanity check
     * that DURATION is wired up and applied at all.
     */
    public function testDurationInHours(): void
    {
        $tz = new \DateTimeZone('UTC');

        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=3;DURATION=PT2H',
            new \DateTime('2014-01-10 09:00:00', $tz),
            null,
            'UTC'
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(3, $computed);

        $this->assertEquals(new \DateTime('2014-01-10 09:00:00', $tz), $computed[0]?->getStart());
        $this->assertEquals(new \DateTime('2014-01-10 11:00:00', $tz), $computed[0]?->getEnd());

        $this->assertEquals(new \DateTime('2014-02-10 09:00:00', $tz), $computed[1]?->getStart());
        $this->assertEquals(new \DateTime('2014-02-10 11:00:00', $tz), $computed[1]?->getEnd());

        $this->assertEquals(new \DateTime('2014-03-10 09:00:00', $tz), $computed[2]?->getStart());
        $this->assertEquals(new \DateTime('2014-03-10 11:00:00', $tz), $computed[2]?->getEnd());
    }

    /**
     * DURATION=P1W specifies a nominal duration of 7 calendar days. This test
     * uses UTC to keep the arithmetic straightforward. Note that in a
     * DST-observing timezone, P1W is NOT always 604,800 seconds: any week that
     * contains a clock change will be 604,800 ± 3,600 seconds in wall-clock
     * terms. Calendar arithmetic (->add()) handles this correctly; a
     * fixed-seconds implementation would not.
     */
    public function testDurationInWeeks(): void
    {
        $tz = new \DateTimeZone('UTC');

        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=3;DURATION=P1W',
            new \DateTime('2014-01-10 09:00:00', $tz),
            null,
            'UTC'
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(3, $computed);

        $this->assertEquals(new \DateTime('2014-01-10 09:00:00', $tz), $computed[0]?->getStart());
        $this->assertEquals(new \DateTime('2014-01-17 09:00:00', $tz), $computed[0]?->getEnd());

        $this->assertEquals(new \DateTime('2014-02-10 09:00:00', $tz), $computed[1]?->getStart());
        $this->assertEquals(new \DateTime('2014-02-17 09:00:00', $tz), $computed[1]?->getEnd());

        $this->assertEquals(new \DateTime('2014-03-10 09:00:00', $tz), $computed[2]?->getStart());
        $this->assertEquals(new \DateTime('2014-03-17 09:00:00', $tz), $computed[2]?->getEnd());
    }

    /**
     * DURATION=P1M is the key distinguishing case. "One calendar month" means
     * each occurrence ends one month after its own start date — which is NOT a
     * fixed number of seconds. In 2014:
     *
     *   Jan 1 + P1M = Feb 1  (31 days = 2,678,400 s)
     *   Feb 1 + P1M = Mar 1  (28 days = 2,419,200 s)
     *   Mar 1 + P1M = Apr 1  (31 days = 2,678,400 s)
     *
     * A DTEND-style fixed-seconds implementation would compute the duration from
     * the first occurrence (31 days) and apply that same number of seconds to
     * every subsequent occurrence — landing on Mar 4 instead of Mar 1 for the
     * February occurrence. DURATION must use calendar arithmetic instead.
     */
    public function testDurationInMonthsUsesCalendarArithmetic(): void
    {
        $tz = new \DateTimeZone('UTC');

        $rule = new Rule(
            'FREQ=MONTHLY;COUNT=3;DURATION=P1M',
            new \DateTime('2014-01-01 04:00:00', $tz),
            null,
            'UTC'
        );

        $computed = $this->transformer->transform($rule);

        $this->assertCount(3, $computed);

        // Jan 1 + P1M = Feb 1 (31 days in January)
        $this->assertEquals(new \DateTime('2014-01-01 04:00:00', $tz), $computed[0]?->getStart());
        $this->assertEquals(new \DateTime('2014-02-01 04:00:00', $tz), $computed[0]?->getEnd());

        // Feb 1 + P1M = Mar 1 (28 days in February 2014, not 31)
        // A fixed-seconds implementation would give Mar 4 here (31 days after Feb 1).
        $this->assertEquals(new \DateTime('2014-02-01 04:00:00', $tz), $computed[1]?->getStart());
        $this->assertEquals(new \DateTime('2014-03-01 04:00:00', $tz), $computed[1]?->getEnd());

        // Mar 1 + P1M = Apr 1 (31 days in March)
        $this->assertEquals(new \DateTime('2014-03-01 04:00:00', $tz), $computed[2]?->getStart());
        $this->assertEquals(new \DateTime('2014-04-01 04:00:00', $tz), $computed[2]?->getEnd());
    }

    /**
     * RFC 5545 §3.6.1 forbids a component from specifying both DTEND and
     * DURATION. Rule::loadFromArray() must throw InvalidRRule if both are
     * present in the same rule string.
     */
    public function testDtendAndDurationTogetherThrows(): void
    {
        $this->expectException(InvalidRRule::class);

        new Rule(
            'FREQ=MONTHLY;COUNT=3;DTEND=20140201T040000;DURATION=P1M',
            new \DateTime('2014-01-01 04:00:00')
        );
    }
}

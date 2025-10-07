<?php

namespace Tests\Recurr\Transformer;

use PHPUnit\Framework\Attributes\DataProvider;
use Recurr\Frequency;
use Recurr\Rule;
use Recurr\Transformer\Constraint\AfterConstraint;
use Recurr\Transformer\Constraint\BeforeConstraint;
use Recurr\Transformer\Constraint\BetweenConstraint;

class ArrayTransformerConstraintTest extends ArrayTransformerBase
{
    private static function prependDateTimeClassNames(array $testCases): array
    {
        $data = [];

        foreach (array_keys($testCases) as $n) {
            $immutable = $testCases[$n];
            array_unshift($immutable, \DateTimeImmutable::class);

            $mutable = $testCases[$n];
            array_unshift($mutable, \DateTime::class);
            $data[] = $immutable;
            $data[] = $mutable;
        }

        return $data;
    }

    #[DataProvider('beforeProvider')]
    public function testBefore(
        string $dateTimeClassName,
        int $frequency,
        int $count,
        string $start,
        string $before,
        bool $inc,
        array $expected,
    ): void {
        /** @var \DateTime|\DateTimeImmutable $start */
        $start = new $dateTimeClassName($start);
        /** @var \DateTimeInterface $before */
        $before = new $dateTimeClassName($before);

        $rule = new Rule();
        $rule
            ->setFreq($frequency)
            ->setCount($count)
            ->setStartDate($start);

        $constraint = new BeforeConstraint($before, $inc);
        $computed = $this->transformer->transform($rule, $constraint);

        self::assertCount(count($expected), $computed);

        foreach ($expected as $n => $expectedDate) {
            self::assertEquals(new $dateTimeClassName($expectedDate), $computed[$n]?->getStart());
        }
    }

    public static function beforeProvider(): array
    {
        return self::prependDateTimeClassNames([
            [Frequency::YEARLY, 20, '2014-03-16 04:00:00', '2017-03-16 23:59:59', true, [
                '2014-03-16 04:00:00',
                '2015-03-16 04:00:00',
                '2016-03-16 04:00:00',
                '2017-03-16 04:00:00',
            ]],
            [Frequency::MONTHLY, 5, '2014-03-16 04:00:00', '2014-05-16 04:00:00', false, [
                '2014-03-16 04:00:00',
                '2014-04-16 04:00:00',
            ]],
            [Frequency::MONTHLY, 5, '2014-03-16 04:00:00', '2014-05-16 04:00:00', true, [
                '2014-03-16 04:00:00',
                '2014-04-16 04:00:00',
                '2014-05-16 04:00:00',
            ]],
        ]);
    }

    #[DataProvider('afterProvider')]
    public function testAfter(
        string $dateTimeClassName,
        int $frequency,
        int $count,
        string $start,
        string $after,
        bool $inc,
        bool $countConstraintFailures,
        array $expected,
    ): void {
        /** @var \DateTime|\DateTimeImmutable $start */
        $start = new $dateTimeClassName($start);
        /** @var \DateTimeInterface $after */
        $after = new $dateTimeClassName($after);

        $rule = new Rule();
        $rule
            ->setFreq($frequency)
            ->setCount($count)
            ->setStartDate($start);

        $constraint = new AfterConstraint($after, $inc);
        $computed = $this->transformer->transform($rule, $constraint, $countConstraintFailures);

        self::assertCount(count($expected), $computed);

        foreach ($expected as $n => $expectedDate) {
            self::assertEquals(new $dateTimeClassName($expectedDate), $computed[$n]?->getStart());
        }
    }

    public static function afterProvider(): array
    {
        return self::prependDateTimeClassNames([
            [Frequency::MONTHLY, 5, '2014-03-16 04:00:00', '2020-05-16 04:00:00', false, false, [
                '2020-06-16 04:00:00',
                '2020-07-16 04:00:00',
                '2020-08-16 04:00:00',
                '2020-09-16 04:00:00',
                '2020-10-16 04:00:00',
            ]],
            [Frequency::MONTHLY, 5, '2014-03-16 04:00:00', '2014-05-16 04:00:00', false, true, [
                '2014-06-16 04:00:00',
                '2014-07-16 04:00:00',
            ]],
            [Frequency::MONTHLY, 5, '2014-03-16 04:00:00', '2014-05-16 04:00:00', false, false, [
                '2014-06-16 04:00:00',
                '2014-07-16 04:00:00',
                '2014-08-16 04:00:00',
                '2014-09-16 04:00:00',
                '2014-10-16 04:00:00',
            ]],
            [Frequency::MONTHLY, 5, '2014-03-16 04:00:00', '2014-05-16 04:00:00', true, true, [
                '2014-05-16 04:00:00',
                '2014-06-16 04:00:00',
                '2014-07-16 04:00:00',
            ]],
            [Frequency::MONTHLY, 5, '2014-03-16 04:00:00', '2014-05-16 04:00:00', true, false, [
                '2014-05-16 04:00:00',
                '2014-06-16 04:00:00',
                '2014-07-16 04:00:00',
                '2014-08-16 04:00:00',
                '2014-09-16 04:00:00',
            ]],
        ]);
    }

    #[DataProvider('betweenProvider')]
    public function testBetween(
        string $dateTimeClassName,
        int $frequency,
        string $start,
        string $after,
        string $before,
        bool $inc,
        array $expected,
    ): void {
        /** @var \DateTime|\DateTimeImmutable $start */
        $start = new $dateTimeClassName($start);
        /** @var \DateTimeInterface $after */
        $after = new $dateTimeClassName($after);
        /** @var \DateTimeInterface $before */
        $before = new $dateTimeClassName($before);

        $rule = new Rule();
        $rule
            ->setFreq($frequency)
            ->setStartDate($start);

        $constraint = new BetweenConstraint(after: $after, before: $before, inc: $inc);
        $computed = $this->transformer->transform($rule, $constraint);

        self::assertCount(count($expected), $computed);

        foreach ($expected as $n => $expectedDate) {
            self::assertEquals(new $dateTimeClassName($expectedDate), $computed[$n]?->getStart());
        }
    }

    public static function betweenProvider(): array
    {
        return self::prependDateTimeClassNames([
            [Frequency::MONTHLY, '2014-03-16 04:00:00', '2014-03-16 04:00:00', '2014-07-16 04:00:00', false, [
                '2014-04-16 04:00:00',
                '2014-05-16 04:00:00',
                '2014-06-16 04:00:00',
            ]],
            [Frequency::MONTHLY, '2014-03-16 04:00:00', '2014-03-16 04:00:00', '2014-07-16 04:00:00', true, [
                '2014-03-16 04:00:00',
                '2014-04-16 04:00:00',
                '2014-05-16 04:00:00',
                '2014-06-16 04:00:00',
                '2014-07-16 04:00:00',
            ]],
            [Frequency::WEEKLY, '2017-07-03 09:30:00', '2017-07-16 23:00:00', '2017-07-21 22:59:59', true, [
                '2017-07-17 09:30:00',
            ]],
            [Frequency::DAILY, '2017-07-24 16:15:00', '2017-07-27 00:00:00', '2017-07-30 23:59:59', true, [
                '2017-07-27 16:15:00',
                '2017-07-28 16:15:00',
                '2017-07-29 16:15:00',
                '2017-07-30 16:15:00',
            ]],
            [Frequency::HOURLY, '2017-07-24 16:15:00', '2017-07-24 17:30:00', '2017-07-24 18:30:00', false, [
                '2017-07-24 18:15:00',
            ]],
            [Frequency::MINUTELY, '2017-07-24 16:15:00', '2017-07-24 17:30:00', '2017-07-24 17:40:00', false, [
                '2017-07-24 17:31:00',
                '2017-07-24 17:32:00',
                '2017-07-24 17:33:00',
                '2017-07-24 17:34:00',
                '2017-07-24 17:35:00',
                '2017-07-24 17:36:00',
                '2017-07-24 17:37:00',
                '2017-07-24 17:38:00',
                '2017-07-24 17:39:00',
            ]],
        ]);
    }
}

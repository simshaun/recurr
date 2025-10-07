<?php

declare(strict_types=1);

/*
 * Copyright 2025 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Based on rrule.js
 * Copyright 2010, Jakub Roztocil and Lars Schoning
 * https://github.com/jkbr/rrule/blob/master/LICENCE
 *
 * Based on python-dateutil - Extensions to the standard Python datetime module.
 * Copyright (c) 2003-2011 - Gustavo Niemeyer <gustavo@niemeyer.net>
 * Copyright (c) 2012 - Tomi Pieviläinen <tomi.pievilainen@iki.fi>
 */

namespace Recurr;

use Recurr\Exception\InvalidArgument;
use Recurr\Exception\InvalidRRule;
use Recurr\Exception\InvalidWeekday;

/**
 * A model of a RRULE.
 *
 * http://www.ietf.org/rfc/rfc2445.txt
 *
 * Information not contained in the RRULE is derived from the DTSTART property (default: current datetime).
 * For example, "FREQ=YEARLY;BYMONTH=1" doesn't specify a day or time, so those values come from DTSTART.
 *
 *
 * BYxxx rules modify the recurrence. When a BYxxx rule specifies a time period equal to or larger than
 * the frequency, it reduces occurrences. When it specifies a smaller time period, it expands occurrences.
 *
 * For example,
 * "FREQ=DAILY;BYMONTH=1" reduces the number of occurrences from "all days" to "all days in January".
 * "FREQ=YEARLY;BYMONTH=1,2" increases the number of occurrences each year from 1 to 2.
 *
 *
 * When multiple BYxxx rules are set, then after evaluating the FREQ and INTERVAL parts, the BYxxx rules are applied to
 * the set of evaluated occurrences in the following order:
 *
 * BYMONTH, BYWEEKNO, BYYEARDAY, BYMONTHDAY, BYDAY, BYHOUR, BYMINUTE, BYSECOND and BYSETPOS;
 *
 * Then COUNT or UNTIL limitations are applied.
 *
 * For example,
 *
 * FREQ=YEARLY;INTERVAL=2;BYMONTH=1;BYDAY=SU;BYHOUR=8,9;BYMINUTE=30
 *
 * First, "INTERVAL=2" would apply to "FREQ=YEARLY", resulting in "every other year".
 * Then, "BYMONTH=1" would apply, resulting in "every January, every other year".
 * Then, "BYDAY=SU" would apply, resulting in "every Sunday in January, every other year".
 * Then, "BYHOUR=8,9" would apply, resulting in "every Sunday in January at 8am and 9am, every other year".
 * Then, "BYMINUTE=30" would apply, resulting in "every Sunday in January at 8:30am and 9:30am, every other year".
 * Then, lacking information from the RRULE, the second is derived from DTSTART, resulting in
 *   "every Sunday in January at 8:30:00 AM and 9:30:00 AM, every other year".
 * Similarly, if the BYMINUTE, BYHOUR, BYDAY, BYMONTHDAY or BYMONTH rule part were missing, the appropriate minute,
 *   hour, day or month would be derived from the "DTSTART" property.
 *
 * The following is a RRULE example of "10 meetings that occur every other day": FREQ=DAILY;COUNT=10;INTERVAL=2
 *
 * @author Shaun Simmons <gh@simshaun.com>
 */
class Rule
{
    public const string TZ_FIXED = 'fixed';
    public const string TZ_FLOAT = 'floating';

    /**
     * @var array<string, int>
     */
    public static array $freqs = [
        'YEARLY' => 0,
        'MONTHLY' => 1,
        'WEEKLY' => 2,
        'DAILY' => 3,
        'HOURLY' => 4,
        'MINUTELY' => 5,
        'SECONDLY' => 6,
    ];

    protected ?string $timezone = null;

    protected static string $defaultTimezone;

    protected \DateTime|\DateTimeImmutable|null $startDate = null;

    protected \DateTime|\DateTimeImmutable|null $endDate = null;

    protected bool $isStartDateFromDtstart = false;

    /**
     * @see Rule::$freqs
     */
    protected ?int $freq = null;

    protected int $interval = 1;

    protected bool $isExplicitInterval = false;

    protected \DateTime|\DateTimeImmutable|null $until = null;

    protected ?int $count = null;

    /**
     * @var int[]|null
     */
    protected ?array $bySecond = null;

    /**
     * @var int[]|null
     */
    protected ?array $byMinute = null;

    /**
     * @var int[]|null
     */
    protected ?array $byHour = null;

    /**
     * @var string[]|null
     */
    protected ?array $byDay = null;

    /**
     * @var int[]|null
     */
    protected ?array $byMonthDay = null;

    /**
     * @var int[]|null
     */
    protected ?array $byYearDay = null;

    /**
     * @var int[]|null
     */
    protected ?array $byWeekNumber = null;

    /**
     * @var int[]|null
     */
    protected ?array $byMonth = null;

    protected string $weekStart = 'MO';
    protected bool $weekStartDefined = false;

    /**
     * @var array<string, int>
     */
    protected array $days = [
        'MO' => 0,
        'TU' => 1,
        'WE' => 2,
        'TH' => 3,
        'FR' => 4,
        'SA' => 5,
        'SU' => 6,
    ];

    /**
     * @var int[]|null
     */
    protected ?array $bySetPosition = null;

    /**
     * @var DateInclusion[]|null
     */
    protected ?array $rDates = null;

    /**
     * @var DateExclusion[]|null
     */
    protected ?array $exDates = null;

    /**
     * Construct a new Rule.
     *
     * @param array<string, string>|string|null $rrule
     *
     * @throws InvalidRRule
     */
    public function __construct(
        array|string|null $rrule = null,
        \DateTime|\DateTimeImmutable|string|null $startDate = null,
        \DateTime|\DateTimeImmutable|string|null $endDate = null,
        ?string $timezone = null,
    ) {
        static::$defaultTimezone = date_default_timezone_get();

        if (empty($timezone)) {
            if ($startDate instanceof \DateTimeInterface) {
                $timezone = $startDate->getTimezone()->getName();
            } else {
                $timezone = static::$defaultTimezone;
            }
        }
        $this->setTimezone($timezone);

        if (\is_string($startDate)) {
            $startDate = new \DateTime($startDate, new \DateTimeZone($timezone));
        }
        if ($startDate) {
            $this->setStartDate($startDate);
        }

        if (\is_string($endDate)) {
            $endDate = new \DateTime($endDate, new \DateTimeZone($timezone));
        }
        if ($endDate) {
            $this->setEndDate($endDate);
        }

        if (is_array($rrule)) {
            $this->loadFromArray($rrule);
        } elseif ($rrule !== null && $rrule !== '' && $rrule !== '0') {
            $this->loadFromString($rrule);
        }
    }

    /**
     * Create a Rule object based on a RRULE string.
     *
     * @throws InvalidRRule
     */
    public static function createFromString(
        string $rrule,
        \DateTime|\DateTimeImmutable|string|null $startDate = null,
        \DateTime|\DateTimeImmutable|string|null $endDate = null,
        ?string $timezone = null,
    ): self {
        return new self($rrule, $startDate, $endDate, $timezone);
    }

    /**
     * Create a Rule object based on a RRULE array.
     *
     * @param array<string, string> $rrule
     *
     * @throws InvalidRRule
     */
    public static function createFromArray(
        array $rrule,
        \DateTime|\DateTimeImmutable|string|null $startDate = null,
        \DateTime|\DateTimeImmutable|string|null $endDate = null,
        ?string $timezone = null,
    ): self {
        return new self($rrule, $startDate, $endDate, $timezone);
    }

    /**
     * Create a Rule object based on natural language text.
     *
     * @param string $text Natural language description like "every day for 3 times"
     * @param \DateTime|\DateTimeImmutable|string|null $startDate Start date for the recurrence
     * @param \DateTime|\DateTimeImmutable|string|null $endDate End date for the recurrence
     * @param string|null $timezone Timezone identifier
     *
     * @return self
     *
     * @throws InvalidRRule If the text cannot be parsed
     */
    public static function createFromText(
        string $text,
        \DateTime|\DateTimeImmutable|string|null $startDate = null,
        \DateTime|\DateTimeImmutable|string|null $endDate = null,
        ?string $timezone = null,
    ): self {
        $parser = new TextParser();
        $options = $parser->parseText($text);
        
        if ($options === null) {
            throw new InvalidRRule('Unable to parse text: ' . $text);
        }
        
        return new self($options, $startDate, $endDate, $timezone);
    }

    /**
     * Populate the object based on a RRULE string.
     *
     * @param string $rrule RRULE string
     * Populate the model from a RRULE string.
     *
     * @throws InvalidRRule
     */
    public function loadFromString(string $rrule): static
    {
        $rrule = strtoupper($rrule);
        $rrule = trim($rrule, ';');
        $rrule = trim($rrule, "\n");
        $rows = explode("\n", $rrule);

        $parts = [];
        foreach ($rows as $rruleForRow) {
            $parts = array_merge($parts, $this->parseString($rruleForRow));
        }

        return $this->loadFromArray($parts);
    }

    /**
     * Parse string for parts
     *
     * @return array<string, string>
     *
     * @throws InvalidRRule
     */
    public function parseString(string $rrule): array
    {
        if (str_starts_with($rrule, 'DTSTART:')) {
            $pieces = explode(':', $rrule);

            if (count($pieces) !== 2) {
                throw new InvalidRRule('DTSTART is not valid');
            }

            return ['DTSTART' => $pieces[1]];
        }

        if (str_starts_with($rrule, 'RRULE:')) {
            $rrule = str_replace('RRULE:', '', $rrule);
        }

        $pieces = array_filter(explode(';', $rrule));
        $parts = [];

        if (count($pieces) === 0) {
            throw new InvalidRRule('RRULE is empty');
        }

        // Split each piece of the RRULE in to KEY=>VAL
        foreach ($pieces as $piece) {
            if (!str_contains($piece, '=')) {
                continue;
            }

            [$key, $val] = explode('=', $piece);
            $parts[$key] = $val;
        }

        return $parts;
    }

    /**
     * Populate the object from an array of RRULE parts.
     *
     * @param array<string, mixed> $parts
     *
     * @throws InvalidRRule
     */
    public function loadFromArray(array $parts): static
    {
        // FREQ is required
        if (!isset($parts['FREQ'])) {
            throw new InvalidRRule('FREQ is required');
        } elseif (!\is_string($parts['FREQ'])) {
            throw new InvalidRRule('FREQ must be a string');
        } else {
            if (!isset(self::$freqs[$parts['FREQ']])) {
                throw new InvalidRRule('FREQ is invalid');
            }

            $this->setFreq(self::$freqs[$parts['FREQ']]);
        }

        $timezone = new \DateTimeZone($this->getTimezone() ?: static::$defaultTimezone);

        // DTSTART
        if (isset($parts['DTSTART']) && \is_string($parts['DTSTART'])) {
            $this->isStartDateFromDtstart = true;
            $date = new \DateTime($parts['DTSTART']);
            $date = $date->setTimezone($timezone);
            $this->setStartDate($date);
        }

        // DTEND
        if (isset($parts['DTEND']) && \is_string($parts['DTEND'])) {
            $date = new \DateTime($parts['DTEND']);
            $date = $date->setTimezone($timezone);
            $this->setEndDate($date);
        }

        // UNTIL or COUNT
        if (isset($parts['UNTIL']) && isset($parts['COUNT'])) {
            throw new InvalidRRule('UNTIL and COUNT must not exist together in the same RRULE');
        } elseif (isset($parts['UNTIL']) && \is_string($parts['UNTIL'])) {
            $date = new \DateTime($parts['UNTIL']);
            $date = $date->setTimezone($timezone);
            $this->setUntil($date);
        } elseif (isset($parts['COUNT']) && \is_numeric($parts['COUNT'])) {
            $this->setCount((int) $parts['COUNT']);
        }

        // INTERVAL
        if (isset($parts['INTERVAL']) && \is_numeric($parts['INTERVAL'])) {
            $this->setInterval((int) $parts['INTERVAL']);
        }

        // BYSECOND
        if (isset($parts['BYSECOND'])) {
            $this->setBySecond($this->parseCommaSeparatedInts($parts['BYSECOND']));
        }

        // BYMINUTE
        if (isset($parts['BYMINUTE'])) {
            $this->setByMinute($this->parseCommaSeparatedInts($parts['BYMINUTE']));
        }

        // BYHOUR
        if (isset($parts['BYHOUR'])) {
            $this->setByHour($this->parseCommaSeparatedInts($parts['BYHOUR']));
        }

        // BYDAY
        if (isset($parts['BYDAY'])) {
            $this->setByDay($this->parseCommaSeparatedStrings($parts['BYDAY']));
        }

        // BYMONTHDAY
        if (isset($parts['BYMONTHDAY'])) {
            $this->setByMonthDay($this->parseCommaSeparatedInts($parts['BYMONTHDAY']));
        }

        // BYYEARDAY
        if (isset($parts['BYYEARDAY'])) {
            $this->setByYearDay($this->parseCommaSeparatedInts($parts['BYYEARDAY']));
        }

        // BYWEEKNO
        if (isset($parts['BYWEEKNO'])) {
            $this->setByWeekNumber($this->parseCommaSeparatedInts($parts['BYWEEKNO']));
        }

        // BYMONTH
        if (isset($parts['BYMONTH'])) {
            $this->setByMonth($this->parseCommaSeparatedInts($parts['BYMONTH']));
        }

        // BYSETPOS
        if (isset($parts['BYSETPOS'])) {
            $this->setBySetPosition($this->parseCommaSeparatedInts($parts['BYSETPOS']));
        }

        // WKST
        if (isset($parts['WKST']) && \is_string($parts['WKST'])) {
            $this->setWeekStart($parts['WKST']);
        }

        // RDATE
        if (isset($parts['RDATE'])) {
            $this->setRDates($this->parseCommaSeparatedStrings($parts['RDATE']));
        }

        // EXDATE
        if (isset($parts['EXDATE'])) {
            $this->setExDates($this->parseCommaSeparatedStrings($parts['EXDATE']));
        }

        return $this;
    }

    /**
     * Convert the model to an RRULE string.
     */
    public function getString(string $timezoneType = self::TZ_FLOAT): string
    {
        $format = 'Ymd\THis';

        $parts = [];

        // FREQ
        $parts[] = 'FREQ='.$this->getFreqAsText();

        // UNTIL or COUNT
        $until = $this->getUntil();
        $count = $this->getCount();
        if ($until !== null) {
            if ($timezoneType === self::TZ_FIXED) {
                $u = clone $until;
                $u = $u->setTimezone(new \DateTimeZone('UTC'));
                $parts[] = 'UNTIL='.$u->format($format.'\Z');
            } else {
                $parts[] = 'UNTIL='.$until->format($format);
            }
        } elseif ($count !== null && $count !== 0) {
            $parts[] = 'COUNT='.$count;
        }

        // DTSTART
        if ($this->isStartDateFromDtstart && $this->getStartDate()) {
            if ($timezoneType === self::TZ_FIXED) {
                $d = $this->getStartDate();
                $tzid = $d->getTimezone()->getName();
                $date = $d->format($format);
                $parts[] = "DTSTART;TZID=$tzid:$date";
            } else {
                $parts[] = 'DTSTART='.$this->getStartDate()->format($format);
            }
        }

        // DTEND
        if ($this->getEndDate()) {
            if ($timezoneType === self::TZ_FIXED) {
                $d = $this->getEndDate();
                $tzid = $d->getTimezone()->getName();
                $date = $d->format($format);

                $parts[] = "DTEND;TZID=$tzid:$date";
            } else {
                $parts[] = 'DTEND='.$this->getEndDate()->format($format);
            }
        }

        // INTERVAL
        $interval = $this->getInterval();
        if ($this->isExplicitInterval && $interval !== 0) {
            $parts[] = 'INTERVAL='.$interval;
        }

        // BYSECOND
        $bySecond = $this->getBySecond();
        if ($bySecond !== null && count($bySecond) > 0) {
            $parts[] = 'BYSECOND='.implode(',', $bySecond);
        }

        // BYMINUTE
        $byMinute = $this->getByMinute();
        if ($byMinute !== null && count($byMinute) > 0) {
            $parts[] = 'BYMINUTE='.implode(',', $byMinute);
        }

        // BYHOUR
        $byHour = $this->getByHour();
        if ($byHour !== null && count($byHour) > 0) {
            $parts[] = 'BYHOUR='.implode(',', $byHour);
        }

        // BYDAY
        $byDay = $this->getByDay();
        if ($byDay !== null && count($byDay) > 0) {
            $parts[] = 'BYDAY='.implode(',', $byDay);
        }

        // BYMONTHDAY
        $byMonthDay = $this->getByMonthDay();
        if ($byMonthDay !== null && count($byMonthDay) > 0) {
            $parts[] = 'BYMONTHDAY='.implode(',', $byMonthDay);
        }

        // BYYEARDAY
        $byYearDay = $this->getByYearDay();
        if ($byYearDay !== null && count($byYearDay) > 0) {
            $parts[] = 'BYYEARDAY='.implode(',', $byYearDay);
        }

        // BYWEEKNO
        $byWeekNumber = $this->getByWeekNumber();
        if ($byWeekNumber !== null && count($byWeekNumber) > 0) {
            $parts[] = 'BYWEEKNO='.implode(',', $byWeekNumber);
        }

        // BYMONTH
        $byMonth = $this->getByMonth();
        if ($byMonth !== null && count($byMonth) > 0) {
            $parts[] = 'BYMONTH='.implode(',', $byMonth);
        }

        // BYSETPOS
        $bySetPosition = $this->getBySetPosition();
        if ($bySetPosition !== null && count($bySetPosition) > 0) {
            $parts[] = 'BYSETPOS='.implode(',', $bySetPosition);
        }

        // WKST
        $weekStart = $this->getWeekStart();
        if ($this->weekStartDefined && !empty($weekStart)) {
            $parts[] = 'WKST='.$weekStart;
        }

        // RDATE
        $rDates = $this->getRDates();
        if (!empty($rDates)) {
            $dates = [];
            foreach ($rDates as $inclusion) {
                $format = 'Ymd';
                if ($inclusion->hasTime) {
                    $format .= '\THis';
                    if ($inclusion->isUtcExplicit) {
                        $format .= '\Z';
                    }
                }
                $dates[] = $inclusion->date->format($format);
            }
            $parts[] = 'RDATE='.implode(',', $dates);
        }

        // EXDATE
        $exDates = $this->getExDates();
        if (!empty($exDates)) {
            $dates = [];
            foreach ($exDates as $exclusion) {
                $format = 'Ymd';
                if ($exclusion->hasTime) {
                    $format .= '\THis';
                    if ($exclusion->isUtcExplicit) {
                        $format .= '\Z';
                    }
                }
                $dates[] = $exclusion->date->format($format);
            }
            $parts[] = 'EXDATE='.implode(',', $dates);
        }

        return implode(';', $parts);
    }

    /**
     * @see http://www.php.net/manual/en/timezones.php
     */
    public function setTimezone(string $timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    /**
     * Declares the first occurrence date.
     *
     * @param \DateTime|\DateTimeImmutable $startDate Date of the first occurrence in the recurrence
     * @param bool|null $includeInString If true, include date as DTSTART when calling getString()
     */
    public function setStartDate(\DateTime|\DateTimeImmutable $startDate, ?bool $includeInString = null): static
    {
        $this->startDate = $startDate;

        if ($includeInString !== null) {
            $this->isStartDateFromDtstart = $includeInString;
        }

        return $this;
    }

    public function getStartDate(): \DateTime|\DateTimeImmutable|null
    {
        return $this->startDate;
    }

    /**
     * The date of the last possible occurrence in the set.
     */
    public function setEndDate(\DateTime|\DateTimeImmutable|null $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getEndDate(): \DateTime|\DateTimeImmutable|null
    {
        return $this->endDate;
    }

    /**
     * Declare the frequency of occurrences.
     *
     * May be one of:
     *  - Frequency::SECONDLY to specify repeating events based on an
     *    interval of a second or more.
     *  - Frequency::MINUTELY to specify repeating events based on an
     *    interval of a minute or more.
     *  - Frequency::HOURLY to specify repeating events based on an
     *    interval of an hour or more.
     *  - Frequency::DAILY to specify repeating events based on an
     *    interval of a day or more.
     *  - Frequency::WEEKLY to specify repeating events based on an
     *    interval of a week or more.
     *  - Frequency::MONTHLY to specify repeating events based on an
     *    interval of a month or more.
     *  - Frequency::YEAR to specify repeating events based on an
     *    interval of a year or more.
     *
     * @throws InvalidArgument
     */
    public function setFreq(int|string $freq): static
    {
        if (is_string($freq)) {
            if (!array_key_exists($freq, self::$freqs)) {
                throw new InvalidArgument('Frequency must comply with RFC 2445.');
            } else {
                $freq = self::$freqs[$freq];
            }
        }

        if ($freq < 0 || $freq > 6) {
            throw new InvalidArgument('Frequency integer must be 0 through 6; Use the class constants.');
        }

        $this->freq = $freq;

        return $this;
    }

    /**
     * An internal representation of recurrence frequency.
     *
     * @see getFreqAsText
     */
    public function getFreq(): ?int
    {
        return $this->freq;
    }

    public function getFreqAsText(): ?string
    {
        if ($this->freq === null) {
            return null;
        }

        $result = array_search($this->getFreq(), self::$freqs, true);

        return $result !== false ? $result : null;
    }

    /**
     * Declare the interval between occurrences.
     *
     * The default value is `1`, meaning every second for a SECONDLY rule, every minute for a MINUTELY rule, every hour
     * for an HOURLY rule, every day for a DAILY rule, every week for a WEEKLY rule, every month for a MONTHLY rule,
     * and every year for a YEARLY rule.
     *
     * @param int $interval positive integer that represents how often the RRULE repeats
     *
     * @throws InvalidArgument
     */
    public function setInterval(int $interval): static
    {
        if ($interval < 1) {
            throw new InvalidArgument('Interval must be a positive integer');
        }

        $this->interval = $interval;
        $this->isExplicitInterval = true;

        return $this;
    }

    /**
     * Get the interval that represents how often the RRULE repeats.
     */
    public function getInterval(): int
    {
        return $this->interval;
    }

    /**
     * Declare a date that occurrences in the set must not exceed.
     *
     * Note: Either COUNT or UNTIL may be specified, but not both.
     *       This will nullify the COUNT limit if it exists.
     */
    public function setUntil(\DateTime|\DateTimeImmutable|null $until): static
    {
        $this->until = $until;
        $this->count = null;

        return $this;
    }

    public function getUntil(): \DateTime|\DateTimeImmutable|null
    {
        $date = $this->until;

        if ($date !== null
            && $date->getTimezone()->getName() === 'UTC'
            && $this->getTimezone() !== 'UTC'
        ) {
            $timestamp = $date->getTimestamp();
            $date = $date->setTimezone(new \DateTimeZone($this->getTimezone() ?: static::$defaultTimezone));
            $date = $date->setTimestamp($timestamp);
        }

        return $date;
    }

    /**
     * Limits the number of dates returned that satisfy the RRULE.
     *
     * Note: Either COUNT or UNTIL may be specified, but not both.
     *       This will nullify the UNTIL limit if it exists.
     *
     * @param int|null $count Number of occurrences
     */
    public function setCount(?int $count): static
    {
        $this->count = $count;
        $this->until = null;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * @param int[]|null $bySecond Valid values: 0-59
     */
    public function setBySecond(?array $bySecond): static
    {
        if ($bySecond === null) {
            $this->bySecond = null;

            return $this;
        }

        foreach ($bySecond as $second) {
            if ($second < 0 || $second > 59) {
                throw new InvalidRRule("Second must be 0-59, got: $second");
            }
        }

        $this->bySecond = $bySecond;

        return $this;
    }

    /**
     * @return int[]|null
     */
    public function getBySecond(): ?array
    {
        return $this->bySecond;
    }

    /**
     * @param int[]|null $byMinute Valid values: 0-59
     */
    public function setByMinute(?array $byMinute): static
    {
        if ($byMinute === null) {
            $this->byMinute = null;

            return $this;
        }

        foreach ($byMinute as $minute) {
            if ($minute < 0 || $minute > 59) {
                throw new InvalidRRule("Minute must be 0-59, got: $minute");
            }
        }

        $this->byMinute = $byMinute;

        return $this;
    }

    /**
     * @return int[]|null
     */
    public function getByMinute(): ?array
    {
        return $this->byMinute;
    }

    /**
     * @param int[]|null $byHour Valid values: 0-23
     */
    public function setByHour(?array $byHour): static
    {
        if ($byHour === null) {
            $this->byHour = null;

            return $this;
        }

        foreach ($byHour as $hour) {
            if ($hour < 0 || $hour > 23) {
                throw new InvalidRRule("Hour must be 0-23, got: $hour");
            }
        }

        $this->byHour = $byHour;

        return $this;
    }

    /**
     * @return int[]|null
     */
    public function getByHour(): ?array
    {
        return $this->byHour;
    }

    /**
     * An array of days of the week.
     *
     * | Value | Represents  |
     * | ----- | ----------- |
     * | MO    | Monday      |
     * | TU    | Tuesday     |
     * | WE    | Wednesday   |
     * | TH    | Thursday    |
     * | FR    | Friday      |
     * | SA    | Saturday    |
     * | SU    | Sunday      |
     *
     * Values can be "positional" if preceded by a positive (+n) or negative (-n) integer.
     * Positional values represent a "nth occurrence of day". They only work with MONTHLY or YEARLY rule frequencies.
     *
     * For example, with a MONTHLY frequency, +1MO (or simply 1MO) represents the first Monday of the month,
     * whereas -1MO represents the last Monday of the month.
     *
     * If the value isn't positional (i.e. it's just "FR"), then it represents all of those days within the frequency.
     *
     * For example, with a MONTHLY rule, "MO" represents all Mondays in the month.
     *
     * -------------------------------------------------------------------------
     * DO NOT MIX NON-POSITIONAL AND POSITIONAL DAYS.
     * This isn't supported and will trigger an error.
     * e.g. "MO" and "-2FR"
     * -------------------------------------------------------------------------
     *
     * @param string[]|null $byDay Valid values: MO, TU, WE, TH, FR, SA, SU (optionally prefixed with +/-n)
     *
     * @throws InvalidRRule
     */
    public function setByDay(?array $byDay): static
    {
        if ($byDay === null) {
            $this->byDay = $byDay;

            return $this;
        }

        if (count($byDay) === 0) {
            throw new InvalidRRule('BYDAY must contain at least one value');
        }

        // Check for mixing positional and non-positional days
        $hasPositional = false;
        $hasNonPositional = false;
        foreach ($byDay as $day) {
            if (preg_match('/\d/', $day)) {
                $hasPositional = true;
            } else {
                $hasNonPositional = true;
            }
        }
        if ($hasPositional && $hasNonPositional) {
            throw new InvalidRRule('Cannot mix positional (e.g. -1MO) and non-positional (e.g. MO) BYDAY values');
        }

        $isMoreGranularThanMonthly = $this->getFreq() > static::$freqs['MONTHLY'];
        if ($isMoreGranularThanMonthly && $hasPositional) {
            throw new InvalidRRule('Positional BYDAY (e.g. -1MO) only works with MONTHLY and YEARLY frequencies');
        }

        $this->byDay = $byDay;

        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getByDay(): ?array
    {
        return $this->byDay;
    }

    /**
     * @return Weekday[]
     *
     * @deprecated Since v6. Use getByDayAsWeekdays
     */
    public function getByDayTransformedToWeekdays(): array
    {
        return $this->getByDayAsWeekdays();
    }

    /**
     * @return Weekday[]
     *
     * @throws InvalidWeekday
     */
    public function getByDayAsWeekdays(): array
    {
        $byDay = $this->getByDay();

        if (empty($byDay)) {
            return [];
        }

        $weekdays = [];
        foreach ($byDay as $day) {
            if (strlen($day) === 2) {
                $weekdays[] = new Weekday($day, null);
            } else {
                preg_match('/^([+-]?\d+)([A-Z]{2})$/', $day, $dayParts);
                $weekdays[] = new Weekday($dayParts[2], (int) $dayParts[1]);
            }
        }

        return $weekdays;
    }

    /**
     * Limits occurrences to specific days of the month.
     *
     * Examples:
     * [5, 10] represents the 5th and 10th day of the month.
     * [-10] represents the tenth to the last day of the month.
     *
     * @param int[]|null $byMonthDay Valid values: 1-31 or -31 through -1
     */
    public function setByMonthDay(?array $byMonthDay): static
    {
        $this->byMonthDay = $byMonthDay;

        return $this;
    }

    /**
     * @return int[]|null
     */
    public function getByMonthDay(): ?array
    {
        return $this->byMonthDay;
    }

    /**
     * Limits occurrences to specific days of the year.
     *
     * Examples:
     * [10] represents the 10th day of the year (January 10th)
     * [-1] represents the last day of the year (December 31st)
     *
     * @param int[]|null $byYearDay Valid values: 1-366, or -366 through -1
     */
    public function setByYearDay(?array $byYearDay): static
    {
        $this->byYearDay = $byYearDay;

        return $this;
    }

    /**
     * @return int[]|null
     */
    public function getByYearDay(): ?array
    {
        return $this->byYearDay;
    }

    /**
     * An array of integers representing weeks of the year.
     *
     * Only valid for YEARLY frequency.
     *
     * Values correspond to weeks according to the week numbering as defined in ISO 8601.
     * A week is defined as a seven-day period, starting on the day of the week declared as the week start.
     *   (see setWeekStart)
     *
     * Week 1 of the calendar year is the first week which contains at least four days in that calendar year.
     *
     * Examples:
     * [3] represents the 3rd week of the year
     * [1,5] represents the 1st and 5th weeks of the year
     *
     * Note: Assuming a Monday week start, week 53 can only occur when Thursday is January 1st or if it's a leap year
     * and Wednesday is January 1.
     *
     * @param int[]|null $byWeekNumber Valid values: 1-53, or -53 through -1
     */
    public function setByWeekNumber(?array $byWeekNumber): static
    {
        $this->byWeekNumber = $byWeekNumber;

        return $this;
    }

    /**
     * @return int[]|null
     */
    public function getByWeekNumber(): ?array
    {
        return $this->byWeekNumber;
    }

    /**
     * An array of integers representing months of the year.
     *
     * @param int[]|null $byMonth Valid values: 1-12
     */
    public function setByMonth(?array $byMonth): static
    {
        $this->byMonth = $byMonth;

        return $this;
    }

    /**
     * @return int[]|null
     */
    public function getByMonth(): ?array
    {
        return $this->byMonth;
    }

    public function hasByMonth(): bool
    {
        $val = $this->getByMonth();

        return $val !== null && count($val) > 0;
    }

    /**
     * Declares the day on which the workweek starts.
     *
     * This is significant in a couple cases:
     * 1. A WEEKLY frequency has an interval greater than 1 and a BYDAY rule.
     * 2. A YEARLY frequency has a BYWEEKNO rule.
     *
     * The default value is MO (Monday).
     *
     * @param string $weekStart valid values: MO, TU, WE, TH, FR, SA and SU
     *
     * @throws InvalidArgument
     */
    public function setWeekStart(string $weekStart): static
    {
        $weekStart = strtoupper($weekStart);

        if (!in_array($weekStart, ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'])) {
            throw new InvalidArgument('Week Start must be one of MO, TU, WE, TH, FR, SA, SU');
        }

        $this->weekStart = $weekStart;
        $this->weekStartDefined = true;

        return $this;
    }

    public function getWeekStart(): string
    {
        return $this->weekStart;
    }

    /**
     * @return int 0-6, 0=Monday, 6=Sunday
     */
    public function getWeekStartAsNum(): int
    {
        $weekStart = $this->getWeekStart();

        return $this->days[$weekStart];
    }

    /**
     * An array of values that represents the "nth occurrence" within the set of events specified by the rule.
     *
     * Must only be used in conjunction with another BYxxx rule.
     *
     * Examples:
     * "the last work day of the month" could be represented as `FREQ=MONTHLY;BYDAY=MO,TU,WE,TH,FR;BYSETPOS=-1`
     *
     * @param int[]|null $bySetPosition Valid values: 1-366, or -366 through -1
     */
    public function setBySetPosition(?array $bySetPosition): static
    {
        $this->bySetPosition = $bySetPosition;

        return $this;
    }

    /**
     * @return int[]|null
     */
    public function getBySetPosition(): ?array
    {
        return $this->bySetPosition;
    }

    /**
     * An array of dates to include in the generated set regardless of RRULE limits.
     *
     * @param string[]|DateInclusion[]|null $rDates array of dates to include
     */
    public function setRDates(?array $rDates): static
    {
        if ($rDates === null) {
            $this->rDates = [];

            return $this;
        }

        $timezone = new \DateTimeZone($this->getTimezone() ?: static::$defaultTimezone);

        $dates = [];
        foreach ($rDates as $val) {
            if ($val instanceof DateInclusion) {
                $val->date = $this->convertZtoUtc($val->date);
                $dates[] = $val;
            } else {
                $date = new \DateTime($val, $timezone);
                $dates[] = new DateInclusion(
                    $this->convertZtoUtc($date),
                    str_contains($val, 'T'),
                    str_contains($val, 'Z')
                );
            }
        }

        $this->rDates = $dates;

        return $this;
    }

    /**
     * @return DateInclusion[]|null
     */
    public function getRDates(): ?array
    {
        return $this->rDates;
    }

    /**
     * An array of dates to exclude from the generated set.
     *
     * @param string[]|DateExclusion[]|null $exDates
     */
    public function setExDates(?array $exDates): static
    {
        if ($exDates === null) {
            $this->exDates = null;

            return $this;
        }

        $timezone = new \DateTimeZone($this->getTimezone() ?: static::$defaultTimezone);

        $dates = [];
        foreach ($exDates as $val) {
            if ($val instanceof DateExclusion) {
                $val->date = $this->convertZtoUtc($val->date);
                $dates[] = $val;
            } else {
                $date = new \DateTime($val, $timezone);
                $dates[] = new DateExclusion(
                    $this->convertZtoUtc($date),
                    str_contains($val, 'T'),
                    str_contains($val, 'Z')
                );
            }
        }

        $this->exDates = $dates;

        return $this;
    }

    /**
     * @return DateExclusion[]|null
     */
    public function getExDates(): ?array
    {
        return $this->exDates;
    }

    /**
     * DateTime::setTimezone fails if the timezone does not have an ID.
     * "Z" is the same as "UTC", but "Z" does not have an ID.
     *
     * This is necessary for exclusion dates to be handled properly.
     */
    private function convertZtoUtc(\DateTime|\DateTimeImmutable $date): \DateTime|\DateTimeImmutable
    {
        if ($date->getTimezone()->getName() !== 'Z') {
            return $date;
        }

        return $date->setTimezone(new \DateTimeZone('UTC'));
    }

    public function repeatsIndefinitely(): bool
    {
        return !$this->getCount() && !$this->getUntil() && !$this->getEndDate();
    }

    /**
     * @param array<mixed> $values
     *
     * @return int[]
     */
    private function mapToInt(array $values): array
    {
        $result = [];
        foreach ($values as $value) {
            if (is_numeric($value) || is_string($value)) {
                $result[] = (int) $value;
            }
        }

        return $result;
    }

    /**
     * @return string[]|null
     */
    private function parseCommaSeparatedStrings(mixed $value): ?array
    {
        if (!\is_string($value) && !\is_numeric($value)) {
            return null;
        }

        return array_filter(
            explode(',', (string) $value),
            static fn ($v) => $v !== '',
        );
    }

    /**
     * @return int[]|null
     */
    private function parseCommaSeparatedInts(mixed $value): ?array
    {
        if (!(\is_string($value) || \is_numeric($value))) {
            return null;
        }

        return $this->mapToInt(explode(',', (string) $value));
    }
}

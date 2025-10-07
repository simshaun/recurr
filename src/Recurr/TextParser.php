<?php

declare(strict_types=1);

/*
 * Copyright 2025 Shaun Simmons
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Based on rrule.js text parsing functionality
 * Copyright 2010, Jakub Roztocil and Lars Schoning
 * https://github.com/jkbr/rrule/blob/master/LICENCE
 */

namespace Recurr;

use Recurr\Exception\InvalidArgument;

/**
 * Parses natural language text into Rule options.
 *
 * Based on the rrule.js text parsing functionality.
 * Supports phrases like:
 * - "every day"
 * - "every 2 weeks"
 * - "every day for 3 times"
 * - "every Monday"
 * - "every month on the 15th"
 *
 * @author Fabiano Lothor <fabiano.lothor@gmail.com>
 */
class TextParser
{
    /**
     * @var array<string, string>
     */
    private array $tokens = [];
    
    private string $text = '';
    
    private ?string $symbol = null;
    
    /**
     * @var array<string>|null
     */
    private ?array $value = null;
    
    private bool $done = true;
    
    /**
     * @var array<string, string>
     */
    private array $options = [];
    
    public function __construct()
    {
        $this->initializeTokens();
    }
    
    /**
     * Parse natural language text into Rule options array.
     *
     * @param string $text Natural language text describing recurrence
     *
     * @return array<string, string>|null Options array suitable for Rule constructor
     */
    public function parseText(string $text): ?array
    {
        $this->options = [];
        $this->text = strtolower(trim($text));
        $this->done = false;
        $this->symbol = null;
        $this->value = null;
        
        if (!$this->nextSymbol()) {
            return null;
        }
        
        try {
            $this->parseStatement();
            return $this->options;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Initialize the token patterns for text parsing.
     */
    private function initializeTokens(): void
    {
        $this->tokens = [
            'SKIP' => '/^[ \r\n\t]+|^\.$/i',
            'number' => '/^[1-9][0-9]*/',
            'every' => '/^every/i',
            'days' => '/^days?/i',
            'weekdays' => '/^weekdays?/i',
            'weeks' => '/^weeks?/i',
            'hours' => '/^hours?/i',
            'minutes' => '/^minutes?/i',
            'months' => '/^months?/i',
            'years' => '/^years?/i',
            'on' => '/^(on|in)/i',
            'at' => '/^(at)/i',
            'the' => '/^the/i',
            'first' => '/^first/i',
            'second' => '/^second/i',
            'third' => '/^third/i',
            'last' => '/^last/i',
            'for' => '/^for/i',
            'times' => '/^times?/i',
            'until' => '/^(un)?til/i',
            'monday' => '/^mo(n(day)?)?/i',
            'tuesday' => '/^tu(e(s(day)?)?)?/i',
            'wednesday' => '/^we(d(n(esday)?)?)?/i',
            'thursday' => '/^th(u(r(sday)?)?)?/i',
            'friday' => '/^fr(i(day)?)?/i',
            'saturday' => '/^sa(t(urday)?)?/i',
            'sunday' => '/^su(n(day)?)?/i',
            'january' => '/^jan(uary)?/i',
            'february' => '/^feb(ruary)?/i',
            'march' => '/^mar(ch)?/i',
            'april' => '/^apr(il)?/i',
            'may' => '/^may/i',
            'june' => '/^june?/i',
            'july' => '/^july?/i',
            'august' => '/^aug(ust)?/i',
            'september' => '/^sep(t(ember)?)?/i',
            'october' => '/^oct(ober)?/i',
            'november' => '/^nov(ember)?/i',
            'december' => '/^dec(ember)?/i',
            'comma' => '/^(,\s*|(and|or)\s*)+/i',
        ];
    }
    
    /**
     * Move to the next symbol in the text.
     *
     * @return bool True if a symbol was found, false if parsing is complete
     */
    private function nextSymbol(): bool
    {
        $this->symbol = null;
        $this->value = null;
        $best = null;
        $bestSymbol = null;
        
        do {
            if ($this->done) {
                return false;
            }
            
            $best = null;
            foreach ($this->tokens as $name => $regex) {
                if (preg_match($regex, $this->text, $matches)) {
                    if ($best === null || strlen($matches[0]) > strlen($best[0])) {
                        $best = $matches;
                        $bestSymbol = $name;
                    }
                }
            }
            
            if ($best !== null) {
                $this->text = substr($this->text, strlen($best[0]));
                if ($this->text === '') {
                    $this->done = true;
                }
            }
            
            if ($best === null) {
                $this->done = true;
                $this->symbol = null;
                $this->value = null;
                return false;
            }
        } while ($bestSymbol === 'SKIP');
        
        $this->symbol = $bestSymbol;
        $this->value = $best;
        return true;
    }
    
    /**
     * Accept a specific symbol if it matches the current symbol.
     *
     * @param string $name Symbol name to match
     *
     * @return array<string>|false Matched symbol data or false if no match
     */
    private function accept(string $name): array|false
    {
        if ($this->symbol === $name) {
            $value = $this->value;
            $this->nextSymbol();
            return $value ?? false;
        }
        return false;
    }
    
    /**
     * Expect a specific symbol and throw exception if not found.
     *
     * @param string $name Symbol name to expect
     *
     * @return bool Always returns true if symbol is found
     *
     * @throws InvalidArgument If expected symbol is not found
     */
    private function expect(string $name): bool
    {
        if ($this->accept($name)) {
            return true;
        }
        throw new InvalidArgument("Expected $name but found " . $this->symbol);
    }
    
    /**
     * Accept a number symbol.
     *
     * @return array<string>|false Number data or false if current symbol is not a number
     */
    private function acceptNumber(): array|false
    {
        return $this->accept('number');
    }
    
    /**
     * Check if parsing is complete.
     *
     * @return bool True if parsing is done
     */
    private function isDone(): bool
    {
        return $this->done && $this->symbol === null;
    }
    
    /**
     * Parse a complete recurrence statement.
     *
     * Handles patterns like "every [n] frequency [for count times]".
     *
     * @throws InvalidArgument If statement cannot be parsed
     */
    private function parseStatement(): void
    {
        // every [n]
        $this->expect('every');
        $n = $this->acceptNumber();
        if ($n) {
            $this->options['INTERVAL'] = (string) intval($n[0]);
        }
        
        if ($this->isDone()) {
            throw new InvalidArgument('Unexpected end');
        }
        
        switch ($this->symbol) {
            case 'days':
                $this->options['FREQ'] = 'DAILY';
                $this->nextSymbol();
                break;
                
            case 'weekdays':
                $this->options['FREQ'] = 'WEEKLY';
                $this->options['BYDAY'] = 'MO,TU,WE,TH,FR';
                $this->nextSymbol();
                break;
                
            case 'weeks':
                $this->options['FREQ'] = 'WEEKLY';
                $this->nextSymbol();
                break;
                
            case 'hours':
                $this->options['FREQ'] = 'HOURLY';
                $this->nextSymbol();
                break;
                
            case 'minutes':
                $this->options['FREQ'] = 'MINUTELY';
                $this->nextSymbol();
                break;
                
            case 'months':
                $this->options['FREQ'] = 'MONTHLY';
                $this->nextSymbol();
                break;
                
            case 'years':
                $this->options['FREQ'] = 'YEARLY';
                $this->nextSymbol();
                break;
                
            case 'monday':
            case 'tuesday':
            case 'wednesday':
            case 'thursday':
            case 'friday':
            case 'saturday':
            case 'sunday':
                $this->options['FREQ'] = 'WEEKLY';
                $dayMap = [
                    'monday' => 'MO',
                    'tuesday' => 'TU',
                    'wednesday' => 'WE',
                    'thursday' => 'TH',
                    'friday' => 'FR',
                    'saturday' => 'SA',
                    'sunday' => 'SU'
                ];
                if ($this->symbol !== null && isset($dayMap[$this->symbol])) {
                    $this->options['BYDAY'] = $dayMap[$this->symbol];
                }
                $this->nextSymbol();
                break;
                
            case 'january':
            case 'february':
            case 'march':
            case 'april':
            case 'may':
            case 'june':
            case 'july':
            case 'august':
            case 'september':
            case 'october':
            case 'november':
            case 'december':
                $this->options['FREQ'] = 'YEARLY';
                $monthMap = [
                    'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
                    'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
                    'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12
                ];
                if ($this->symbol !== null && isset($monthMap[$this->symbol])) {
                    $this->options['BYMONTH'] = (string) $monthMap[$this->symbol];
                }
                $this->nextSymbol();
                break;
                
            default:
                throw new InvalidArgument('Unknown symbol: ' . $this->symbol);
        }
        
        // Handle "for X times" or "until date"
        if ($this->symbol === 'for') {
            $this->nextSymbol();
            $count = $this->acceptNumber();
            if ($count) {
                $this->options['COUNT'] = (string) intval($count[0]);
                $this->accept('times'); // optional "times" after number
            }
        } elseif ($this->symbol === 'until') {
            // Simple until handling - could be extended for date parsing
            $this->nextSymbol();
            // For now, we'll skip complex date parsing
        }
    }
}

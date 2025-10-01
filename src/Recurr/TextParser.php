<?php

namespace Recurr;

use Recurr\Exception\InvalidArgument;

/**
 * TextParser - Parses natural language text into Rule options
 * 
 * Based on the rrule.js text parsing functionality
 * Supports phrases like:
 * - "every day"
 * - "every 2 weeks" 
 * - "every day for 3 times"
 * - "every Monday"
 * - "every month on the 15th"
 */
class TextParser
{
    private $tokens = [];
    private $text = '';
    private $symbol = null;
    private $value = null;
    private $done = true;
    private $options = [];
    
    public function __construct()
    {
        $this->initializeTokens();
    }
    
    /**
     * Parse natural language text into Rule options array
     * 
     * @param string $text Natural language text describing recurrence
     * @return array Options array suitable for Rule constructor
     */
    public function parseText($text)
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
    
    private function initializeTokens()
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
    
    private function nextSymbol()
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
    
    private function accept($name)
    {
        if ($this->symbol === $name) {
            $value = $this->value;
            $this->nextSymbol();
            return $value;
        }
        return false;
    }
    
    private function expect($name)
    {
        if ($this->accept($name)) {
            return true;
        }
        throw new InvalidArgument("Expected $name but found " . $this->symbol);
    }
    
    private function acceptNumber()
    {
        return $this->accept('number');
    }
    
    private function isDone()
    {
        return $this->done && $this->symbol === null;
    }
    
    private function parseStatement()
    {
        // every [n]
        $this->expect('every');
        $n = $this->acceptNumber();
        if ($n) {
            $this->options['INTERVAL'] = intval($n[0]);
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
                $this->options['BYDAY'] = $dayMap[$this->symbol];
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
                $this->options['BYMONTH'] = $monthMap[$this->symbol];
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
                $this->options['COUNT'] = intval($count[0]);
                $this->accept('times'); // optional "times" after number
            }
        } elseif ($this->symbol === 'until') {
            // Simple until handling - could be extended for date parsing
            $this->nextSymbol();
            // For now, we'll skip complex date parsing
        }
    }
}

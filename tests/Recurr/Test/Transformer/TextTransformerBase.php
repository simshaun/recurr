<?php

namespace Recurr\Test\Transformer;

use Recurr\Transformer\TextTransformer;

class TextTransformerBase extends \PHPUnit_Framework_TestCase
{
    protected $dateTime;

    /** @var TextTransformer */
    protected $transformer;

    public function setUp()
    {
        setlocale(LC_ALL, 'en_US');
        $this->dateTime    = new \DateTime('2014-03-16 04:00:00');
        $this->transformer = new TextTransformer();
    }
}

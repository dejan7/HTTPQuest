<?php

namespace HTTPQuest\Tests\Unit;

use HTTPQuest\Decoders\Decoder;
use HTTPQuest\Decoders\DecoderOptions;

class DecoderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetValuesAsArray()
    {
        $d = new Decoder(
            __DIR__ . "/urlencoded.txt",
            "application/json",
            0,
            new DecoderOptions()
        );

        $this->assertEquals(['post' => [], 'files' => []], $d->getValuesAsArray());
    }
}
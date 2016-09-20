<?php

namespace HTTPQuest\Tests\Unit\Decoders;

use HTTPQuest\Decoders\JSONDecoder;

class JSONDecoderTest extends \PHPUnit_Framework_TestCase
{
    public function testDecode()
    {
        $decoder = new JSONDecoder(
            __DIR__ . "/json.txt",
            "application/json",
            0
        );

        $expected = [
            'post' => [
                'foo' => [
                    "innerFoo" => "innerBar"
                ]
            ],
            'files' => []
        ];

        $actual = $decoder->decode();

        $this->assertEquals($expected, $actual);
    }
}
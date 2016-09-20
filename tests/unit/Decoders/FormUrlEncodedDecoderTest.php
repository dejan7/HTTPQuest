<?php

namespace HTTPQuest\Tests\Unit\Decoders;

use HTTPQuest\Decoders\FormUrlEncodedDecoder;

class FormUrlEncodedDecoderTest extends \PHPUnit_Framework_TestCase
{
    public function testDecode()
    {
        $decoder = new FormUrlEncodedDecoder(
            __DIR__ . "/urlencoded.txt",
            "application/json",
            0
        );

        $expected = [
            'post' => [
                "foo1" => "bar1",
                "foo2" => "bar2"
            ],
            'files' => []
        ];

        $actual = $decoder->decode();

        $this->assertEquals($expected, $actual);
    }
}
<?php

namespace HTTPQuest\Tests\Unit\Decoders;

use HTTPQuest\Decoders\FormDataDecoder;

class FormDataDecoderTest extends \PHPUnit_Framework_TestCase
{
    public function testDecode()
    {
        $decoder = new FormDataDecoder(
            __DIR__ . "/formdata.txt",
            "multipart/form-data; boundary=---011000010111000001101001",
            0
        );

        $actual = $decoder->decode();

        $expected = [
                "foo1" => "bar1",
                "foo2" => "bar2"
        ];

        $this->assertArrayHasKey('image', $actual['files']);
        $this->assertEquals($actual['files']['image']['name'], '1.jpg');
        $this->assertEquals($expected, $decoder->getValuesAsArray()['post']);
    }
}
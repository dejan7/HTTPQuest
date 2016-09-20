<?php

namespace HTTPQuest\Tests\Unit\Decoders;

use HTTPQuest\Decoders\FormDataDecoder;
use HTTPQuest\Exceptions\DecodeException;

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
        $this->assertEquals('1.jpg', $actual['files']['image']['name'][0]);
        $this->assertEquals('2.jpg', $actual['files']['image']['name'][1]);
        $this->assertEquals($expected, $decoder->getValuesAsArray()['post']);
    }

    public function testBoundaryException()
    {
        $this->expectException(DecodeException::class);

        $decoder = new FormDataDecoder(
            __DIR__ . "/formdata.txt",
            "abc",
            0
        );

        $decoder->decode();
    }

    public function testInvalidDirException()
    {
        $this->expectException(\RuntimeException::class);

        $decoder = new FormDataDecoder(
            "invalid dir",
            "multipart/form-data; boundary=---011000010111000001101001",
            0
        );

        $decoder->decode();
    }

    public function testNoContentDisposition()
    {
        $this->expectException(DecodeException::class);

        $decoder = new FormDataDecoder(
            __DIR__ . "/formdata-invalid1.txt",
            "multipart/form-data; boundary=---011000010111000001101001",
            0
        );

        $decoder->decode();
    }

    public function testAbrubtEndOfHTTPBody()
    {
        $this->expectException(DecodeException::class);

        $decoder = new FormDataDecoder(
            __DIR__ . "/formdata-invalid2.txt",
            "multipart/form-data; boundary=---011000010111000001101001",
            0
        );

        $decoder->decode();
    }
}
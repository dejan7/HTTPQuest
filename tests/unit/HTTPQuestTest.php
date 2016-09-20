<?php

namespace HTTPQuest\Tests\Unit;

use HTTPQuest\ContentTypes;
use HTTPQuest\HTTPQuest;
use HTTPQuest\Requests;

class HTTPQuestTest extends \PHPUnit_Framework_TestCase
{
    public function testDecodeJson() {
        $server = [
            'REQUEST_METHOD' => Requests::PUT,
            'CONTENT_TYPE' => ContentTypes::JSON
        ];

        $expected = [
            'foo' => [
                "innerFoo" => "innerBar"
            ]
        ];

        $h = new HTTPQuest($server, __DIR__ . "/Decoders/json.txt");
        $h->decode($actualBody, $actualFiles);

        $this->assertEquals($expected, $actualBody);
    }

    public function testDecodeFormData() {
        $server = [
            'REQUEST_METHOD' => Requests::PUT,
            'CONTENT_TYPE' => "multipart/form-data; boundary=---011000010111000001101001"
        ];

        $expected = [
            "foo1" => "bar1",
            "foo2" => "bar2"
        ];

        $h = new HTTPQuest($server, __DIR__ . "/Decoders/formdata.txt");
        $h->decode($actualBody, $actualFiles);



        $this->assertArrayHasKey('image', $actualFiles);
        $this->assertEquals('1.jpg', $actualFiles['image']['name'][0]);
        $this->assertEquals('2.jpg', $actualFiles['image']['name'][1]);
        $this->assertEquals($expected, $actualBody);
    }

    public function testDecodeFormUrlEncoded() {
        $server = [
            'REQUEST_METHOD' => Requests::PUT,
            'CONTENT_TYPE' => ContentTypes::X_WWW_FORM_URLENCODED
        ];

        $expected = [
            "foo1" => "bar1",
            "foo2" => "bar2"
        ];

        $h = new HTTPQuest($server, __DIR__ . "/Decoders/urlencoded.txt");
        $h->decode($actualBody, $actualFiles);

        $this->assertEquals($expected, $actualBody);
    }

    public function testNoParamsConstructor()
    {
        $h = new HTTPQuest();
        $h->decode($actualBody, $actualFiles);

        $this->assertEquals(null, $actualBody);
        $this->assertEquals(null, $actualFiles);
    }
}
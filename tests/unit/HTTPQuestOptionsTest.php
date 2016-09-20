<?php

namespace HTTPQuest\Tests\Unit;

use HTTPQuest\ContentTypes;
use HTTPQuest\Exceptions\NoActiveMethodException;
use HTTPQuest\HTTPQuestOptions;
use HTTPQuest\Requests;

class HTTPQuestOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testForMethod()
    {
        $o = new HTTPQuestOptions();
        $o = $o->forMethod(Requests::POST);

        $this->assertInstanceOf(HTTPQuestOptions::class, $o);
    }

    public function testParse()
    {
        $o = new HTTPQuestOptions();
        $o = $o->forMethod(Requests::POST)->parse(ContentTypes::X_WWW_FORM_URLENCODED);

        $this->assertInstanceOf(HTTPQuestOptions::class, $o);
    }

    public function testGetOptions()
    {
        $o = new HTTPQuestOptions();
        $o->forMethod(Requests::POST)
            ->parse(ContentTypes::FORMDATA);

        $expected = ContentTypes::FORMDATA;
        $actual = $o->getOption('POST', "multipart/form-data; boundary=----g6h8");
        $this->assertEquals($expected, $actual);

        $actual = $o->getOption("GET", "application/json");
        $this->assertNull($actual);

        $actual = $o->getOption("POST", "application/json");
        $this->assertNull($actual);
    }

    public function testParseException()
    {
        $this->expectException(NoActiveMethodException::class);
        $o = new HTTPQuestOptions();
        $o->parse("gg");
    }
}
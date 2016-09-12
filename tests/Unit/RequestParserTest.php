<?php

namespace RESTQuest\Tests\Unit;

use RESTQuest\RequestParser;

class RequestParserTest extends \PHPUnit_Framework_TestCase
{

    public function testParseFormData()
    {
        $requestParser = new RequestParser();
        $rawText = "------WebKitFormBoundaryQqbY5qc1dIqd2okf\r\nContent-Disposition: form-data; name=\"name\"\r\n\r\nJohn Doe\r\n------WebKitFormBoundaryQqbY5qc1dIqd2okf\r\nContent-Disposition: form-data; name=\"about\"\r\n\r\nengineer\r\n------WebKitFormBoundaryQqbY5qc1dIqd2okf\r\nContent-Disposition: form-data; name=\"something\"\r\n\r\nelse\r\n------WebKitFormBoundaryQqbY5qc1dIqd2okf--";

        preg_match('/boundary=(.*)$/', "multipart/form-data; boundary=----WebKitFormBoundaryQqbY5qc1dIqd2okf", $matches);

        $requestParser->parseFormData($rawText, $matches[1]);

        $this->assertEquals($_POST, [
            "name"      => "John Doe",
            "about"     => "engineer",
            "something" => "else"
        ]);
    }

    public function testParseXWWWFormUrlencoded()
    {
        $requestParser = new RequestParser();
        $urlencoded = "foo1=bar1&foo2=bar2";
        $requestParser->parseXWWWFormUrlencoded($urlencoded);

        $this->assertEquals($_POST, [
            "foo1" => "bar1",
            "foo2" => "bar2"
        ]);
    }

    public function testParseJson()
    {
        $requestParser = new RequestParser();
        $jsonText = '{"foo": {"bar" : {"a":1, "b":2} } }';
        $requestParser->parseJSON($jsonText);

        $this->assertEquals($_POST, [
            "foo" => [
                "bar" => [
                    "a" => 1,
                    "b" => 2
                ]
            ]
        ]);
    }

}
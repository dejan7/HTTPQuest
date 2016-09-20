<?php

namespace HTTPQuest\Tests\Unit\Decoders;

use HTTPQuest\Decoders\DecoderOptions;

class DecoderOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMaxVars()
    {
        $o = new DecoderOptions();
        $expected = ini_get('max_input_vars');

        $this->assertEquals($expected, $o->getMaxVars());
    }

    public function testGetPostDataReadingEnabled()
    {
        $o = new DecoderOptions();
        $expected = (bool) ini_get('enable_post_data_reading');

        $this->assertEquals($expected, $o->getPostDataReadingEnabled());
    }
}
<?php

namespace HTTPQuest\Decoders;

class FormUrlEncodedDecoder extends Decoder
{
    public function decode()
    {
        $input = file_get_contents($this->path, "r");

        parse_str($input, $data);

        if ($data !== null) {
            $this->post = $data;
        }

        return $this->getValuesAsArray();
    }
}
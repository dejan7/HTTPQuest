<?php

namespace RESTQuest\Decoders;

class JSONDecoder extends Decoder
{
    public function decode()
    {
        $input = file_get_contents($this->path, "r");

        $maxNestingLevels = $this->options->getMaxNestingLevels();
        $data = json_decode($input, true, $maxNestingLevels);

        if ($data !== null) {
            $this->post = $data;
        }

        return $this->getValuesAsArray();
    }
}
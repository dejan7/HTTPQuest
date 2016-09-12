<?php

Namespace RESTQuest;

class RequestParser
{
    public function parseFormData(&$requestRawBody, $formBoundary)
    {
        // split content by boundary and get rid of last -- element
        $rawBlocks = preg_split("/-+$formBoundary/", $requestRawBody);
        array_pop($rawBlocks);

        $data = [];
        foreach ($rawBlocks as $id => $block)
        {
            if (empty($block))
                continue;

            //skip files
            if (strpos($block, 'filename=') !== false)
            {
                continue;
            }
            // parse  other fields
            else
            {
                // match "name" and optional value in between newline sequences
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
            }

            if (isset($matches[1]) && isset($matches[2]))
                $data[$matches[1]] = $matches[2];
        }

        if (is_array($data))
            $_POST = $data;
        else
            $_POST = [];

    }

    public function parseXWWWFormUrlencoded(&$requestRawBody)
    {
        parse_str($requestRawBody, $data);

        if (is_array($data))
            $_POST = $data;
        else
            $_POST = [];
    }

    public function parseJSON(&$requestRawBody)
    {
        $data = json_decode($requestRawBody, true);

        if (is_array($data))
            $_POST = $data;
        else
            $_POST = [];
    }
}
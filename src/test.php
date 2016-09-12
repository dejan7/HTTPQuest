<?php
/*
include __DIR__ . "/../vendor/autoload.php";

$RESTquest = new \RESTQuest\RESTQuest();
$RESTquest->parse();

print_r($_POST);

$input = file_get_contents('php://input');
print_r($input);

// grab multipart boundary from content type header
preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
$boundary = $matches[1];

// split content by boundary and get rid of last -- element
$a_blocks = preg_split("/-+$boundary/", $input);
array_pop($a_blocks);
//print_r($a_blocks);
$a_data = [];
foreach ($a_blocks as $id => $block)
{
    if (empty($block))
        continue;

    if (strpos($block, 'filename=') !== false)
    {
        //we are skipping files
        continue;
    }
    // parse all other fields
    else
    {
        // match "name" and optional value in between newline sequences
        preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
    }
    $a_data[$matches[1]] = $matches[2];
}

print_r($a_data);*/
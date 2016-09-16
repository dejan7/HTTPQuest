<?php
/**
 * Example with manual method/content type configuration
 */

include __DIR__ . "/../vendor/autoload.php";

use RESTQuest\RESTQuestOptions;
use RESTQuest\Requests;
use RESTQuest\ContentTypes;


$opts = new RESTQuestOptions();

$opts->forMethod(Requests::GET)
    ->parse(ContentTypes::X_WWW_FORM_URLENCODED);

$opts->forMethod(Requests::PATCH)
    ->parse(ContentTypes::FORMDATA)
    ->parse(ContentTypes::JSON);

$RESTQuest = new \RESTQuest\RESTQuest(
    $_SERVER,
    "php://input",
    $opts
);

$RESTQuest->decode($myPost, $myFiles);

print_r($myPost);
print_r($myFiles);


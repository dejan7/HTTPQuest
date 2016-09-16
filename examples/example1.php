<?php
/**
 * Basic example
 */

include __DIR__ . "/../vendor/autoload.php";

$RESTQuest = new \RESTQuest\RESTQuest();
$RESTQuest->decode($_POST, $_FILES);

print_r($_POST);
print_r($files);

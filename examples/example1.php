<?php
/**
 * Basic example
 */

include __DIR__ . "/../vendor/autoload.php";

$RESTQuest = new \HTTPQuest\HTTPQuest();
$RESTQuest->decode($_POST, $_FILES);

print_r($_POST);
print_r($_FILES);

<?php

require_once('../vendor/autoload.php');

use NielsHoppe\AWTP\Importer;

//Mapping PHP errors to exceptions
function exception_error_handler($errno, $errstr, $errfile, $errline) {
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('exception_error_handler');

$config = json_decode(file_get_contents('config.json'), true);
$importer = new Importer($config);
$importer->exec();

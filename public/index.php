<?php

require_once '../vendor/autoload.php';

use \NielsHoppe\AWTP\Server;

//Mapping PHP errors to exceptions
set_error_handler([Functions::class, 'exception_error_handler']);

$config = json_decode(file_get_contents('config.json'), true);
$server = new Server($config);
$server->exec();

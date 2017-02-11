<?php

require_once '../vendor/autoload.php';

use \NielsHoppe\RDFDAV\Functions;
use \NielsHoppe\RDFDAV\Server;

//Mapping PHP errors to exceptions
set_error_handler([Functions::class, 'exception_error_handler']);

$server = new Server();
$server->exec();

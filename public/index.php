<?php

require_once '../vendor/autoload.php';

use NielsHoppe\AWTP\Server\Server;

$config = json_decode(file_get_contents("config.json"), true);
$server = new Server($config);
$server->exec();

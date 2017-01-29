<?php

require_once("../vendor/autoload.php");

use \NielsHoppe\AWTP\Server\CardDAV\VCardBuilder;
use \NielsHoppe\AWTP\Server\Config;
use \NielsHoppe\AWTP\Server\Constants;
use \NielsHoppe\AWTP\Server\MappingQueryBuilder;

$store = ARC2::getStore([
    "db_name" => "scotchbox",
    "db_user" => "root",
    "db_pwd" => "root",
    "store_name" => "test",
    "sem_html_formats" => "rdfa microformats erdf openid dc",
]);

if (!$store->isSetUp()) $store->setUp();
$store->reset();
$store->query("LOAD <http://localhost:8080/examples/rdfa.html>");

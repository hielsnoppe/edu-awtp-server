<?php

require_once("../vendor/autoload.php");

use \NielsHoppe\AWTP\CardDAV\VCardBuilder;
use \NielsHoppe\AWTP\Config;
use \NielsHoppe\AWTP\Constants;
use NielsHoppe\AWTP\ARC\CardStore;

$store = ARC2::getStore([
    "db_name" => "scotchbox",
    "db_user" => "root",
    "db_pwd" => "root",
    "store_name" => "test"
]);

$cardStore = new CardStore($store);
$cardStore->inferMissingTypes();

exit();

/*
if (!$store->isSetUp()) $store->setUp();
$store->reset();
$store->query("LOAD <file:///vagrant/data/addressbook.rdf>");
*/

#$rs = $store->query(Constants::SPARQL_ALL_CARDS_FULL);
$rs = $store->query(Constants::SPARQL_ALL_DATA);
var_dump($rs);

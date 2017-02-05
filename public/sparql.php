<?php

include_once('../vendor/autoload.php');

$config = \NielsHoppe\AWTP\Config::getInstance();

/* MySQL and endpoint configuration */
$config = array_merge($config->getAll([
    "db_host", "db_name", "db_user", "db_pwd"
]), [
    /* store name */
    'store_name' => 'test',

    /* endpoint */
    'endpoint_features' => array(
        'select', 'construct', 'ask', 'describe',
        'load', 'insert', 'delete',
        'dump' /* dump is a special command for streaming SPOG export */
    ),
    'endpoint_timeout' => 60, /* not implemented in ARC2 preview */
    'endpoint_read_key' => '', /* optional */
    'endpoint_write_key' => 'REPLACE_THIS_WITH_SOME_KEY', /* optional, but without one, everyone can write! */
    'endpoint_max_limit' => 250, /* optional */
]);

/* instantiation */
$ep = \ARC2::getStoreEndpoint($config);

if (!$ep->isSetUp()) $ep->setUp(); /* create MySQL tables */

/* request handling */
$ep->handleRequest();
$ep->sendHeaders();
echo $ep->getResult();

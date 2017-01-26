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
    "store_name" => "test"
]);

if (!$store->isSetUp()) $store->setUp();
#$store->reset();
$store->query("LOAD <file:///vagrant/data/addressbook.rdf>");

$builder = new MappingQueryBuilder([
    "vcard" => [
        "vcard" => [
            "fn" => "fn",
            "given-name" => "given-name",
            "family-name" => "family-name",
            "nick" => "nick",
            "hasEmail" => "hasEmail"
        ]
    ],
    "foaf" => [
        "vcard" => [
            "name" => "fn",
            "givenname" => "given-name",
            "family_name" => "family-name",
            "firstname" => "given-name",
            "lastname" => "family-name",
            "nick" => "nickname",
            "homepage" => "hasURL",
            "phone" => "hasTelephone"
        ]
    ],
    "bio" => [
        "bio" => [
            "date" => "date"
        ]
    ]
]);

#$query = Constants::SPARQL_ALL_CLASSES;
#$query = Constants::SPARQL_ALL_CARDS;
#$query = Constants::SPARQL_ALL_EVENTS;

function generateMissingInternalIDs ($store) {

    $query = Constants::SPARQL_ALL_CARDS;
    $query = Constants::SPARQL_ALL_EVENTS;
    $rs = $store->query($query);
    #var_dump($rs); return;

    $updateQuery = [
        Constants::SPARQL_PREFIXES,
        "INSERT INTO <http://ns.nielshoppe.de/people> {"
    ];

    foreach ($rs['result']['rows'] as $row) {
        $uri = $row['event'];
        $id = "TODO";
        array_push($updateQuery, sprintf("<%s> app:id \"%s\" .", $uri, $id));
    }

    array_push($updateQuery, '}');
    $updateQuery = implode("\n", $updateQuery);

    echo($updateQuery); return;
    $rs = $store->query($updateQuery);
}

function getCards ($store, $builder) {

    $query = $builder->getQuery("foaf:Person", "vcard:VCard");
    $rs = $store->query($query);

    foreach ($rs["result"] as $uri => $data) {
        echo("\n\n" . $uri . "\n\n");
        var_dump($data);

        /*
        $builder = new VCardBuilder();
        $builder->readFromRDF($data);
        $card = $builder->getCard();
        echo($card->serialize());
        //*/

        echo("\n");
    }
}

function getEvents ($store, $builder) {

    $query = $builder->getQuery("bio:Birth", "bio:Event");
    $rs = $store->query($query);

    foreach ($rs["result"] as $uri => $data) {
        echo("\n\n" . $uri . "\n\n");
        var_dump($data);
        /*

        $builder = new VCardBuilder();
        $builder->readFromRDF($data);
        $card = $builder->getCard();

        echo($card->serialize());
        //*/
        echo("\n");
    }
}

generateMissingInternalIDs($store);
#getCards($store, $builder);
#getEvents($store, $builder);

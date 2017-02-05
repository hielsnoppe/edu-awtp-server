<?php

require_once('../vendor/autoload.php');

use \NielsHoppe\AWTP\CardDAV\VCardBuilder;
use \NielsHoppe\AWTP\Config;
use \NielsHoppe\AWTP\Constants;
use \NielsHoppe\AWTP\SPARQL\MappingQueryBuilder;
use \Sabre\VObject;

$store = ARC2::getStore([
    "db_name" => "scotchbox",
    "db_user" => "root",
    "db_pwd" => "root",
    "store_name" => "test"
]);

if (!$store->isSetUp()) $store->setUp();
//$store->reset();
//$store->query("LOAD <file:///vagrant/data/addressbook.rdf>");

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

#getCards($store, $builder);
#getEvents($store, $builder);
#echo($builder->getQuery("vcard:Individual", "vcard:VCard"));

#$rs = $store->query(Constants::SPARQL_ALL_DATA);
#var_dump($rs);

//$config = array('auto_extract' => 0);
$parser = \ARC2::getSemHTMLParser();
$parser->parse('http://localhost:8080/examples/rdfa.html');
$parser->extractRDF('rdfa');

$triples = $parser->getTriples();
$n3 = $parser->toNTriples($triples);
echo($n3);

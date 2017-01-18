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
$store->reset();
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
    ]
]);

#$query = Constants::SPARQL_ALL_CLASSES;
#$query = query("vcard:Individual", "vcard:VCard");
$query = $builder->getQuery("foaf:Person", "vcard:VCard");
$query = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX vcard: <http://www.w3.org/2001/vcard-rdf/3.0#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX bio: <http://purl.org/vocab/bio/0.1/>
PREFIX app: <http://vocab.nielshoppe.de/edu-awtp-server/#>


CONSTRUCT {
?card rdf:type vcard:VCard ;
vcard:fn ?a ;
vcard:given-name ?d ;
vcard:family-name ?e ;
vcard:nickname ?f ;
vcard:hasURL ?g ;
vcard:hasTelephone ?h ;
vcard:hasAddress ?i ;
}
WHERE {
?card rdf:type foaf:Person .
OPTIONAL { ?card foaf:name ?a } .
OPTIONAL { ?card foaf:givenname ?b } .
OPTIONAL { ?card foaf:family_name ?c } .
OPTIONAL { ?card foaf:firstname ?d } .
OPTIONAL { ?card foaf:lastname ?e } .
OPTIONAL { ?card foaf:nick ?f } .
OPTIONAL { ?card foaf:homepage ?g } .
OPTIONAL { ?card foaf:phone ?h } .
OPTIONAL { ?card vcard:hasAddress ?i } .
}
SPARQL;

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

echo($query);

/*
{ ?res rdf:type vcard:VCard }
UNION { ?res rdf:type vcard:Kind }
UNION { ?res rdf:type vcard:Individual }
UNION { ?res rdf:type vcard:Organization }
UNION { ?res rdf:type vcard:Location }
UNION { ?res rdf:type vcard:Group }
UNION { ?res rdf:type foaf:Person }
*/

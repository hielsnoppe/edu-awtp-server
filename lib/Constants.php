<?php

namespace NielsHoppe\AWTP\Server;

class Constants {

    /*
     * Namespaces
     */

    const NS_RDF = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
    const NS_RDFS = "http://www.w3.org/2000/01/rdf-schema#";
    const NS_OWL = "http://www.w3.org/2002/07/owl#";

    const NS_VCARD = "http://www.w3.org/2001/vcard-rdf/3.0#";
    const NS_VCAL = "http://www.w3.org/2002/12/cal#";
    const NS_FOAF = "http://xmlns.com/foaf/0.1/";
    const NS_BIO = "http://purl.org/vocab/bio/0.1/";

    const NS_APP = "http://vocab.nielshoppe.de/edu-awtp-server#";

    const PREFIXES = [
        'rdf' => NS_RDF,
        'rdfs' => NS_RDFS,
        'owl' => NS_OWL,
        'vcard' => NS_VCARD,
        'vcal' => NS_VCAL,
        'foaf' => NS_FOAF,
        'bio' => NS_BIO,
        'app' => NS_APP
    ];

    /*
     * SPARQL queries
     */

    const SPARQL_PREFIXES = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX vcard: <http://www.w3.org/2001/vcard-rdf/3.0#>
PREFIX vcal: <http://www.w3.org/2002/12/cal#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX bio: <http://purl.org/vocab/bio/0.1/>
PREFIX app: <http://vocab.nielshoppe.de/edu-awtp-server#>
SPARQL;

    const SPARQL_ALL_DATA = <<<SPARQL
SELECT * WHERE {
  GRAPH ?g { ?s ?p ?o . }
}
LIMIT 10
SPARQL;

    const SPARQL_ALL_CLASSES = <<<SPARQL
SELECT DISTINCT ?class WHERE {
  ?s a ?class .
}
LIMIT 25 OFFSET 0
SPARQL;

    const SPARQL_ALL_CARDS = Constants::SPARQL_PREFIXES . <<<SPARQL

SELECT ?card ?type WHERE {
?card rdf:type ?type
FILTER (?type = foaf:Person || ?type = vcard:Individual)
}
SPARQL;

    const SPARQL_ALL_EVENTS = Constants::SPARQL_PREFIXES . <<<SPARQL

SELECT ?event ?type WHERE {
?event rdf:type ?type
FILTER (?type = bio:Birth || ?type = bio:Event)
}
SPARQL;

    const SPARQL_ALL_VOBJECTS_WOID = Constants::SPARQL_PREFIXES . <<<SPARQL

SELECT ?subject ?type WHERE {
    ?subject a ?type
    OPTIONAL { ?subject app:id ?id }
    FILTER (?type = foaf:Person || ?type = vcard:Individual || ?type = bio:Birth)
    FILTER (!bound(?id))
}
SPARQL;

    const SPARQL_ALL_CARDS_FULL = Constants::SPARQL_PREFIXES . <<<SPARQL

CONSTRUCT {
    ?card rdf:type vcard:VCard ;
    app:originalType ?type ;
    app:id ?id ;
    vcard:fn ?foaf_fn ;
    vcard:fn ?vcard_fn ;
    vcard:given-name ?foaf_given ;
    vcard:given-name ?foaf_given2 ;
    vcard:given-name ?vcard_given ;
    vcard:family-name ?foaf_family ;
    vcard:family-name ?foaf_family2 ;
    vcard:family-name ?vcard_family ;
    vcard:nickname ?foaf_nick ;
    vcard:nickname ?vcard_nick ;
    vcard:hasURL ?foaf_url ;
    vcard:hasURL ?vcard_url ;
    vcard:hasTelephone ?foaf_tel ;
    vcard:hasTelephone ?vcard_tel ;
    vcard:hasEmail ?foaf_email ;
    vcard:hasEmail ?vcard_email ;
}
WHERE {
    ?card a ?type ; app:id ?id .
    FILTER (?type = foaf:Person || ?type = vcard:Individual)

    OPTIONAL { ?card foaf:name ?foaf_fn } .
    OPTIONAL { ?card foaf:givenname ?foaf_given } .
    OPTIONAL { ?card foaf:family_name ?foaf_family } .
    OPTIONAL { ?card foaf:firstname ?foaf_given2 } .
    OPTIONAL { ?card foaf:lastname ?foaf_family2 } .
    OPTIONAL { ?card foaf:nick ?foaf_nick } .
    OPTIONAL { ?card foaf:homepage ?foaf_url } .
    OPTIONAL { ?card foaf:phone ?foaf_tel } .
    OPTIONAL { ?card foaf:mbox ?foaf_email } .

    OPTIONAL { ?card vcard:fn ?vcard_fn } .
    OPTIONAL { ?card vcard:given-name ?vcard_given } .
    OPTIONAL { ?card vcard:family-name ?vcard_family } .
    OPTIONAL { ?card vcard:nickname ?vcard_nick } .
    OPTIONAL { ?card vcard:hasURL ?vcard_url } .
    OPTIONAL { ?card vcard:hasTelephone ?vcard_tel } .
    OPTIONAL { ?card vcard:hasEmail ?vcard_email } .
}
SPARQL;

}

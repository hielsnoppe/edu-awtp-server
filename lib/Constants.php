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

/*
{ ?res rdf:type vcard:VCard }
UNION { ?res rdf:type vcard:Kind }
UNION { ?res rdf:type vcard:Individual }
UNION { ?res rdf:type vcard:Organization }
UNION { ?res rdf:type vcard:Location }
UNION { ?res rdf:type vcard:Group }
UNION { ?res rdf:type foaf:Person }
*/

}

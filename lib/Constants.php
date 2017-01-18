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
    const NS_FOAF = "http://xmlns.com/foaf/0.1/";
    const NS_BIO = "http://purl.org/vocab/bio/0.1/";

    const NS_APP = "http://vocab.nielshoppe.de/edu-awtp-server/#";

    /*
     * SPARQL queries
     */

    const SPARQL_PREFIXES = <<<SPARQL
PREFIX rdf: <self::NS_RDF>
PREFIX rdfs: <{self::NS_RDFS}>
PREFIX owl: <{self::NS_OWL}>
PREFIX vcard: <{self::NS_VCARD}>
PREFIX foaf: <{self::NS_FOAF}>
PREFIX bio: <{self::NS_BIO}>
PREFIX app: <{self::NS_APP}>
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

}

<?php

namespace NielsHoppe\RDFDAV;

class Constants {

    /*
     * Namespaces
     */

    const NS_RDF = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
    const NS_RDFS = "http://www.w3.org/2000/01/rdf-schema#";
    const NS_OWL = "http://www.w3.org/2002/07/owl#";

    #const NS_VCARD = "http://www.w3.org/2001/vcard-rdf/3.0#";
    const NS_VCARD = "http://www.w3.org/2006/vcard/ns#";
    const NS_VCAL = "http://www.w3.org/2002/12/cal#";
    const NS_FOAF = "http://xmlns.com/foaf/0.1/";
    const NS_BIO = "http://purl.org/vocab/bio/0.1/";

    const NS_HCARD = 'http://poshrdf.org/ns/mf#';

    const NS_APP = "http://vocab.nielshoppe.de/edu-awtp-server#";

    const PREFIXES = [
        'rdf' => NS_RDF,
        'rdfs' => NS_RDFS,
        'owl' => NS_OWL,
        'vcard' => NS_VCARD,
        'vcal' => NS_VCAL,
        'foaf' => NS_FOAF,
        'bio' => NS_BIO,
        'hcard' => NS_HCARD,
        'app' => NS_APP
    ];

    /*
     * Inference rules
     */

    /**
     * Heads up! The array notations must be read "from inside out". Example:
     *
     * ['?type1']['rdfs:subClassOf'][0] == '?type2'
     * implies
     * ?type2 rdfs:subClassOf ?type1
     *
     * ['?type']['rdfs:domain'][0] == '?pN';
     * implies
     * ?s ?pN [] => ?s a ?type
     */
    const RULES_TYPES = [
        'vcard:VCard' => [
            'rdfs:subClassOf' => [
                'vcard:Individual', 'vcard:Organization', 'vcard:Location', 'vcard:Group'
            ],
            'owl:equivalentClass' => [
                'hcard:Vcard'
            ],
            'rdfs:domain' => [
                'vcard:given-name',
                'vcard:family-name',
                'vcard:honorific-prefix',
                'vcard:honorific-suffix',
                'vcard:hasTelephone',
                'vcard:hasEmail'
            ]
        ],
        'vcard:Individual' => [
            'owl:equivalentClass' => [
                'foaf:Person'
            ]
        ],
        'vcard:Organization' => [
            'owl:equivalentClass' => [
                'foaf:Organization'
            ]
        ],
        'vcard:Group' => [
            'owl:equivalentClass' => [
                'foaf:Group'
            ]
        ],
        'foaf:Person' => [
            'rdfs:domain' => [
                // Status: stable
                'foaf:knows',
                // Status: testing
                'foaf:currentProject', 'foaf:familyName', 'foaf:firstName',
                'foaf:img', 'foaf:lastName', 'foaf:myersBriggs',
                'foaf:pastProject', 'foaf:plan', 'foaf:publications',
                'foaf:schoolHomepage', 'foaf:workInfoHomepage',
                'foaf:workplaceHomepage',
                // Status: archaic
                'foaf:family_name', 'foaf:geekcode', 'foaf:givenname',
                'foaf:surname'
            ]
        ],
        'hcard:Vcard' => [
            'rdfs:domain' => [
                'hcard:photo'
            ]
        ]
    ];

    const RULES_PROPERTIES = [
        'vcard:fn' => [
            // Status: testing
            'foaf:name',
            // hCard 1.0
            'hcard:fn'
        ],
        'vcard:given-name' => [
            // Status: testing
            'foaf:firstName',
            // Status: archaic
            'foaf:givenname',
            // hCard
            'hcard:given-name'
        ],
        'vcard:family-name' => [
            // Status: testing
            'foaf:familyName', 'foaf:lastName',
            // Status: archaic
            'foaf:family_name', 'foaf:surname',
            // hCard 1.0
            'hcard:family-name'
        ],
        'vcard:nickname' => [
            // Status: testing
            'foaf:nick',
            // hCard 1.0
            'hcard:nickname'
        ],
        'vcard:hasPhoto' => [
            // Status: testing
            'foaf:img',
            // hCard 1.0
            'hcard:photo',
            // vCard mapped property
            'vcard:photo'
        ],
        'vcard:hasURL' => [
            // Status: stable
            'foaf:homepage',
            // hCard 1.0
            'hcard:url'
        ],
        'vcard:hasTelephone' => [
            // Status: testing
            'foaf:phone',
            // hCard 1.0
            'hcard:tel',
            // vCard mapped property
            'vcard:phone'
        ],
        'vcard:hasEmail' => [
            // Status: stable
            'foaf:mbox',
            // hCard 1.0
            'hcard:email',
            // vCard mapped property
            'vcard:email'
        ]
    ];

    /*
     * SPARQL queries
     */

    const SPARQL_PREFIXES = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX vcard: <http://www.w3.org/2006/vcard/ns#>
PREFIX vcal: <http://www.w3.org/2002/12/cal#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX bio: <http://purl.org/vocab/bio/0.1/>
PREFIX hcard: <http://poshrdf.org/ns/mf#>
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
FILTER (?type = foaf:Person || ?type = vcard:Individual || ?type = vcard:VCard)
}
SPARQL;

    const SPARQL_ALL_VCARDS = Constants::SPARQL_PREFIXES . <<<SPARQL
CONSTRUCT {
    ?s a vcard:VCard ; ?p ?o
}
WHERE {
    ?s rdf:type ?type ; ?p ?o .
    FILTER (
        ?type = foaf:Person ||
        ?type = vcard:Individual ||
        ?type = vcard:VCard
    )
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
    FILTER (?type = foaf:Person || ?type = vcard:VCard || ?type = bio:Birth)
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
    vcard:honorific-prefix ?vcard_honorific_prefix ;
    vcard:honorific-suffix ?vcard_honorific_suffix ;
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
    FILTER (?type = foaf:Person || ?type = vcard:Individual || vcard:VCard)

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
    OPTIONAL { ?card vcard:honorific-prefix ?vcard_honorific_prefix } .
    OPTIONAL { ?card vcard:honorific-suffix ?vcard_honorific_suffix } .
    OPTIONAL { ?card vcard:nickname ?vcard_nick } .
    OPTIONAL { ?card vcard:hasURL ?vcard_url } .
    OPTIONAL { ?card vcard:hasTelephone ?vcard_tel } .
    OPTIONAL { ?card vcard:hasEmail ?vcard_email } .
}
SPARQL;

}

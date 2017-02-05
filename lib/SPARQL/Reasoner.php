<?php

namespace NielsHoppe\AWTP\SPARQL;

use NielsHoppe\AWTP\Constants;
use Sabre\DAV;
use Sabre\DAV\UUIDUtil;

class Reasoner {

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
                "vcard:Individual", "vcard:Organization", "vcard:Location", "vcard:Group"
            ]
        ],
        /*
        "vcard:Individual" => [
            'rdfs:subClassOf' => [
                "foaf:Person"
            ]
        ]
        */
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
        ]
    ];

    /**
     * @var \ARC2_Store
     */
    private $store;

    public function __construct ($store) {

        $this->store = $store;
    }

    /**
     * @param array $rules  A set of inference rules. Example:
     *
     *     [ '?type' => [
     *         'rdfs:domain' => [ ?p1, ?p2, ..., ?pN ]
     *     ]]
     *
     *     Such that the following implication holds:
     *     ?s ?pN [] => ?s a ?type
     */

    public function inferTypes ($rules) {

        $triples = [];

        foreach ($rules as $type => $typerules) {

            if (array_key_exists('rdfs:domain', $typerules)) {

                $properties = $typerules['rdfs:domain'];
                $rs = $this->findResourcesByProperty($properties);

                foreach ($rs['result']['rows'] as $row) {

                    $triples[] = [$row['person'], 'rdf:type', $type];
                }
            }
        }

        return $triples;
    }

    public function findResourcesByProperty ($properties) {

        $selectQuery = [
            Constants::SPARQL_PREFIXES,
            'SELECT DISTINCT ?person WHERE { ?person a ?type ; ?p [] .',
            'FILTER (!bound(?type) && ('
        ];

        array_push($selectQuery, implode(' || ', array_map(function ($property) {
            return sprintf('?p = %s', $property);
        }, $properties)));

        array_push($selectQuery, '))', '}');
        $selectQuery = implode("\n", $selectQuery);

        echo($selectQuery);
        return $this->store->query($selectQuery);
    }
}

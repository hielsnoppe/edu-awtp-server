<?php

namespace NielsHoppe\AWTP\SPARQL;

use NielsHoppe\AWTP\Constants;

/**
 * @see https://en.wikipedia.org/wiki/VCard
 * @see https://en.wikipedia.org/wiki/HCard
 * @see http://schema.org/Person
 * @see http://schema.org/Organization
 * @see https://www.w3.org/TR/vcard-rdf/
 * @see http://xmlns.com/foaf/spec/
 *
 * @see http://sparql.org/validate/query
 * @see http://stackoverflow.com/questions/25424787/sparql-queries-with-relational-operator
 * @see http://stackoverflow.com/questions/2930246/exploratory-sparql-queries
 * @see https://wiki.base22.com/display/btg/SPARQL+Query+Examples
 */

class MappingQueryBuilder {

    private $mappings;

    private $isA = [
        "vcard:VCard" => [
            "vcard:Individual", "vcard:Organization", "vcard:Location", "vcard:Group"
        ],
        "vcard:Individual" => [
            "foaf:Person"
        ]
    ];

    public function __construct ($mappings) {

        $this->mappings = $mappings;
    }

    private function vars ($mapping) {

        return array_map(function ($key) {
            return chr($key + 97);
        }, array_keys(array_values($mapping)));
    }

    private function construct ($targetNS, $targetType, $mapping) {

        $q = "?card rdf:type $targetNS:$targetType ;";

        $props = array_values($mapping);
        $vars = $this->vars($mapping);

        foreach (array_combine($props, $vars) as $prop => $var) {
            $q .= "\n$targetNS:$prop ?$var ;";
        }

        return "CONSTRUCT {\n$q\n}\n";
    }

    private function where ($sourceNS, $sourceType, $mapping) {

        $q = "?card rdf:type $sourceNS:$sourceType .";

        $props = array_keys($mapping);
        $vars = $this->vars($mapping);

        foreach (array_combine($props, $vars) as $prop => $var) {

            $q .= "\nOPTIONAL { ?card $sourceNS:$prop ?$var } .";
        }

        return "WHERE {\n$q\n}\n";
    }

    private function query ($sourceNS, $sourceType, $targetNS, $targetType, $mappings) {

        $mapping = $mappings[$sourceNS][$targetNS];

        return Constants::SPARQL_PREFIXES . "\n\n"
                . construct($targetNS, $targetType, $mapping) . "\n"
                . where($sourceNS, $sourceType, $mapping) . "\n"
                ;
    }

    public function getQuery($source, $target) {

        list($sourcePrefix, $sourceType) = explode(":", $source);
        list($targetPrefix, $targetType) = explode(":", $target);

        $mapping = $this->mappings[$sourcePrefix][$targetPrefix];

        $query = Constants::SPARQL_PREFIXES . "\n\n";
        $query .= $this->construct($targetPrefix, $targetType, $mapping);
        $query .= $this->where($sourcePrefix, $sourceType, $mapping);

        return $query;
    }
}

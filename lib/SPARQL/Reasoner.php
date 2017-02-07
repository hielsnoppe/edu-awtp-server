<?php

namespace NielsHoppe\RDFDAV\SPARQL;

use NielsHoppe\RDFDAV\Constants;
use phpDocumentor\Reflection\Type;
use Sabre\DAV;
use Sabre\DAV\UUIDUtil;

class Reasoner {

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

            $resources = [];

            if (array_key_exists('rdfs:domain', $typerules)) {

                $properties = $typerules['rdfs:domain'];
                $rs = $this->findResourcesByProperty($properties);

                if (is_array($rs)) {

                    $resources = array_merge($resources, $rs['result']['rows']);
                }
            }

            if (array_key_exists('rdfs:subClassOf', $typerules)) {

                $subclasses = $typerules['rdfs:subClassOf'];
                $rs = $this->findResourcesByType($subclasses);

                if (is_array($rs)) {

                    $resources = array_merge($resources, $rs['result']['rows']);
                }
            }

            if (array_key_exists('owl:equivalentClass', $typerules)) {

                $equivalents = $typerules['owl:equivalentClass'];
                $rs = $this->findResourcesByType($equivalents);

                if (is_array($rs)) {

                    $resources = array_merge($resources, $rs['result']['rows']);
                }
            }

            foreach ($resources as $resource) {

                $subject = '<' . $resource['s'] . '>';
                $triples[] = [$subject, 'rdf:type', $type];
            }
        }

        return $triples;
    }

    public function inferProperties ($rules) {

        $triples = [];

        foreach ($rules as $predicate => $synonyms) {

            $rs = $this->findTriplesByProperty($synonyms);

            foreach ($rs['result']['rows'] as $row) {

                $subject = '<' . $row['s'] . '>';
                $object = $row['o'];

                switch ($row['o type']) {
                    
                case 'uri':
                    $object = '<' . $object . '>';
                    break;

                case 'literal':
                    $object = '"' . $object . '"';
                    break;

                default:
                    break; // must not happen
                }

                $triples[] = [$subject, $predicate, $object];
            }
        }

        return $triples;
    }

    /*
     * private methods
     */

    private function findResourcesByProperty ($properties) {

        $query = Constants::SPARQL_PREFIXES . <<<SPARQL

SELECT DISTINCT ?s WHERE {
    ?s ?p [] .
    OPTIONAL { ?s a ?type }
SPARQL;

        $query .= "\n" . 'FILTER (!bound(?type))' . "\n";

        $query .= 'FILTER (';
        $query .= implode(' || ', array_map(function ($property) {
            return sprintf('?p = %s', $property);
        }, $properties));
        $query .= ")\n";

        $query .= "\n}";

        return $this->store->query($query);
    }

    private function findResourcesByType ($types) {

        $query = Constants::SPARQL_PREFIXES . <<<SPARQL
SELECT DISTINCT ?s WHERE {
    ?s a ?t
SPARQL;

        $query .= 'FILTER (';
        $query .= implode(' || ', array_map(function ($type) {
            return sprintf('?t = %s', $type);
        }, $types));
        $query .= ")\n";

        $query .= "\n}";

        return $this->store->query($query);
    }

    private function findTriplesByProperty ($properties) {

        $query = Constants::SPARQL_PREFIXES . <<<SPARQL

SELECT ?s ?p ?o WHERE {
    ?s ?p ?o ; a ?type .
SPARQL;

        $query .= 'FILTER (';
        $query .= implode(' || ', array_map(function ($property) {
            return sprintf('?p = %s', $property);
        }, $properties));
        $query .= ")\n";

        $query .= "\n}";

        return $this->store->query($query);
    }
}

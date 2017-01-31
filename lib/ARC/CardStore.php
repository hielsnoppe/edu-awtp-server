<?php

namespace NielsHoppe\AWTP\Server\ARC;

use \ARC2_Store;
use \Asparagus\QueryBuilder;
use \NielsHoppe\AWTP\Server\Constants;
use \Sabre\VObject;

/**
 * @see http://stackoverflow.com/a/2940696
 */
class CardStore {

    /**
     * @var \ARC2_Store $store ARC2 store
     */
    private $store;

    public function __construct(ARC2_Store $store) {

        $this->store = $store;
    }

    public function getCards() {
    }

    //private function inferMissingTypes () {
    public function inferMissingTypes () {

        // ?s foaf:___ [] => ?s a foaf:Person
        $properties = [
            // Status: stable
            'knows',
            // Status: testing
            'currentProject', 'familyName', 'firstName', 'img', 'lastName',
            'myersBriggs', 'pastProject', 'plan', 'publications',
            'schoolHomepage', 'workInfoHomepage', 'workplaceHomepage',
            // Status: archaic
            'family_name', 'geekcode', 'givenname', 'surname'
        ];

        $selectQuery = [
            Constants::SPARQL_PREFIXES,
            "SELECT DISTINCT ?person WHERE { ?person a ?type ; ?p [] .",
            //"FILTER (!bound(?type) && ("
            "FILTER (bound(?type) && ("
        ];

        array_push($selectQuery, implode(' || ', array_map(function ($property) {
            return sprintf("?p = foaf:%s", $property);
        }, $properties)));

        array_push($selectQuery, "))", "}");
        $selectQuery = implode("\n", $selectQuery);

        $rs = $this->query($selectQuery);

        $updateQuery = [
            Constants::SPARQL_PREFIXES,
            "INSERT INTO <http://ns.nielshoppe.de/people> {"
        ];

        foreach ($rs['result']['rows'] as $row) {
            $uri = $row['person'];
            array_push($updateQuery, sprintf("<%s> rdf:type foaf:Person .", $uri));
        }

        array_push($updateQuery, '}');
        $updateQuery = implode("\n", $updateQuery);

        echo($updateQuery);
    }

    /**
     *
     */
    private function generateMissingURIs () {

        $query = Constants::SPARQL_ALL_VOBJECTS_WOID;
        $rs = $store->query($query);

        $updateQuery = [
            Constants::SPARQL_PREFIXES,
            "INSERT INTO <http://ns.nielshoppe.de/people> {"
        ];

        foreach ($rs['result']['rows'] as $row) {
            $uri = $row['subject'];
            $id = $this->generateURI();
            array_push($updateQuery, sprintf("<%s> app:id \"%s\" .", $uri, $id));
        }

        array_push($updateQuery, '}');
        $updateQuery = implode("\n", $updateQuery);

        echo($updateQuery); return;
        $rs = $store->query($updateQuery);
    }

    private function generateURI () {

        return 'TODO';
    }

    private function query($query) {
        return $this->store->query($query);
    }

    /*
    private function query(QueryBuilder $query) {

        $result = $this->store->query($query->format());

        $error = $this->store->getErrors();

        if ($error) return false;

        return $result["result"]["rows"];
    }
    */
}

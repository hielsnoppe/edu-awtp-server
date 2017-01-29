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

    private function inferMissingTypes () {

        // ?s foaf:givenname ?o => ?s a foaf:Person
        $query = Constants::SPARQL_PREFIXES . <<<SPARQL

SELECT ?person WHERE {
    ?person a ?type ; ?p []
    FILTER (!bound(?type) && (
        ?p = foaf:firstname ||
        ?p = foaf:lastname ||
        ?p = foaf:givenname ||
        ?p = foaf:familyname
    ))
}
SPARQL;
        // Add X rdf:type foaf:Person
        $rows = $this->query($query);

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

    private function query(QueryBuilder $query) {

        $this->store->query($query->format());

        $error = $this->store->getErrors();

        if ($error) return false;

        return $result["result"]["rows"];
    }
}

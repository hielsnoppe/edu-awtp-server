<?php

namespace NielsHoppe\RDFDAV\ARC;

use \ARC2_Store;
use \Asparagus\QueryBuilder;
use \NielsHoppe\RDFDAV\Constants;
use \Sabre\VObject;
use Sabre\DAV\UUIDUtil;

/**
 * @see http://stackoverflow.com/a/2940696
 * @see https://github.com/byjg/PHP-SPARQL-Lib
 * @see https://github.com/njh/easyrdf
 * @see https://github.com/semsol/arc2/wiki/Remote-Stores-and-Endpoints
 */
class StoreController {

    /**
     * @var \ARC2_Store $store ARC2 store
     */
    private $store;

    public function __construct (ARC2_Store $store) {

        $this->store = $store;
    }

    /**
     * @todo Filter by URIs
     */
    public function getCards ($cardUris = []) {

        $query = Constants::SPARQL_ALL_VCARDS;

        return $this->store->query($query);
    }

    /**
     * @todo Refactor
     * @param string $cardUri
     */
    public function getCard ($cardUri) {

        $query = Constants::SPARQL_PREFIXES . <<<SPARQL
CONSTRUCT {
    ?s a vcard:VCard ; ?p ?o
}
WHERE {
    ?s rdf:type ?type ; ?p ?o ;
    app:id "$cardUri"
    FILTER (
        ?type = foaf:Person ||
        ?type = vcard:Individual ||
        ?type = vcard:VCard
    )
}
SPARQL;

        #echo $query; exit();
        return $this->store->query($query);
    }

    public function generateIDs () {

        $query = Constants::SPARQL_ALL_VOBJECTS_WOID;
        $rs = $this->store->query($query);

        $triples = [];

        foreach ($rs['result']['rows'] as $row) {

            $id = UUIDUtil::getUUID();
            $triples[] = ['<' . $row['subject'] . '>', 'app:id', "\"$id\""];
        }

        return $triples;
    }

    public function insertTriples ($graph, $triples) {

        $triples = array_map(function ($triple) {
            return vsprintf("%s %s %s .", $triple);
        }, $triples);

        $query = Constants::SPARQL_PREFIXES . "\n"
            . "INSERT INTO <$graph> {" . "\n"
            . implode("\n", $triples) . "\n"
            . '}';

        return $this->store->query($query);
    }
}

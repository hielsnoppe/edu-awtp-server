<?php

namespace NielsHoppe\AWTP\ARC;

use \ARC2_Store;
use \Asparagus\QueryBuilder;
use \NielsHoppe\AWTP\Constants;
use \Sabre\VObject;
use Sabre\DAV\UUIDUtil;

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

    public function generateIDs ($store) {

        $query = Constants::SPARQL_ALL_VOBJECTS_WOID;
        $rs = $store->query($query);

        $triples = [];

        foreach ($rs['result']['rows'] as $row) {

            $id = UUIDUtil::getUUID();
            $triples[] = [$row['subject'], 'app:id', $id];
        }

        $this->insertTriples('http://ns.nielshoppe.de/people', $triples);
    }

    public function insertTriples ($graph, $triples) {

        $triples = array_map(function ($triple) {
            return sprintf("<%s> %s %s .", $triple);
        });

        $query = Constants::SPARQL_PREFIXES . "\n"
            . "INSERT INTO <$graph> {" . "\n"
            . implode("\n", $triples) . "\n"
            . '}';

        $query = implode("\n", $query);

        echo $query; return;
        return $this->store->query($query);
    }
}

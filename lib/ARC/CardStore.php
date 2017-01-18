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

    private static $prefixes = [
        "rdf" => Constants::NS_RDF,
        "rdfs" => Constants::NS_RDFS,

        "vcard" => Constants::NS_VCARD,
        "foaf" => Constants::NS_FOAF,
        "bio" => Constants::NS_BIO,

        "app" => Constants::NS_APP
    ];

    public function __construct(ARC2_Store $store) {

        $this->store = $store;
    }

    public function getCards() {
        //
        $query = new QueryBuilder(self::$queryPrefixes);
        $query->select("?fn")
            ->where("?card", "vcard:fn", "?fn")
            ->where("?card", "rdf:about", $cardUri);
        $rows = $this->query($query);
    }

    private function createInternalURIs () {

        $query = new QueryBuilder(self::$prefixes);
        $query->select("")
            ->where();

        $rs = $this->store->query($query);
    }

    private function query(QueryBuilder $query) {

        $this->store->query($query->format());

        $error = $this->store->getErrors();

        if ($error) return false;

        return $result["result"]["rows"];
    }
}

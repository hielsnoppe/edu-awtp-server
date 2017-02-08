<?php

namespace NielsHoppe\RDFDAV;

use NielsHoppe\RDFDAV\ARC\StoreController;
use NielsHoppe\RDFDAV\SPARQL\Reasoner;
use Psr\Log\LoggerInterface;

class Importer {

    /**
     * @var \ARC2_Store
     */
    private $store;
    /**
     * Logger
     */
    private $log;

    public function __construct (LoggerInterface $log) {

        $this->log = $log;

        $arcConfig = Config::getInstance()->getAll([
            "db_host", "db_name", "db_user", "db_pwd"
        ]);
        $arcConfig["store_name"] = "test";
        $arcConfig["sem_html_formats"] = "rdfa microformats erdf openid dc";

        $this->store = \ARC2::getStore($arcConfig);
    }

    #public function load ($store, $source, $graph) {

    /**
     * @param string $source
     */
    public function load ($source) {

        $query = "LOAD <$source>"; // INTO <$graph>
        $this->log->info($query);
        #$store->query($query);
        $this->store->query($query);
    }

    public function process () {

        $graph = Config::getInstance()->get('dev_graph_name');

        $controller = new StoreController($this->store);
        $reasoner = new Reasoner($this->store);

        // Step 1: Ensure that all relevant resources have a type
        $this->log->info('Find and infer missing types...');
        $triples = $reasoner->inferTypes(Constants::RULES_TYPES);
        $controller->insertTriples($graph, $triples);
        $this->log->info(sprintf('Done. Found %u new triples.', count($triples)));

        // Step 2: Ensure that all relevant resources have an internal identifier
        $this->log->info('Find and generate missing IDs...');
        $triples = $controller->generateIDs();
        $controller->insertTriples($graph, $triples);
        $this->log->info(sprintf('Done. Found %u new triples.', count($triples)));

        // Step 3: Translate properties from known vocabularies to vCard
        $this->log->info('Translate properties from known vocabularies to vCard...');
        $triples = $reasoner->inferProperties(Constants::RULES_PROPERTIES);
        $controller->insertTriples($graph, $triples);
        $this->log->info(sprintf('Done. Found %u new triples.', count($triples)));
    }
}

<?php

namespace NielsHoppe\AWTP;

use NielsHoppe\AWTP\ARC\StoreController;
use NielsHoppe\AWTP\SPARQL\Reasoner;
use Sabre\DAV;

class Importer {

    /**
     * @var Config
     */
    private $config;
    /**
     * @var \ARC2_Store
     */
    private $store;

    private $sources = [
        //'http://localhost:8080/example.html',
        'file:///vagrant/public/example.html'
    ];

    public function __construct ($config) {

        $this->config = Config::getInstance();
        $this->config->setAll($config);

        $arcConfig = $this->config->getAll([
            "db_host", "db_name", "db_user", "db_pwd"
        ]);
        $arcConfig["store_name"] = "test";
        $arcConfig["sem_html_formats"] = "rdfa microformats erdf openid dc";

        $this->store = \ARC2::getStore($arcConfig);
    }

    public function exec () {

        $graph = $this->config->get('dev_graph_name');
        
        foreach ($this->sources as $source) {

            $this->store->query("LOAD <$source>"); // INTO <$graph>
        }

        $controller = new StoreController($this->store);
        $reasoner = new Reasoner($this->store);

        // Step 1: Ensure that all relevant resources have a type
        $triples = $reasoner->inferTypes(Constants::RULES_TYPES);
        $controller->insertTriples($graph, $triples);

        // Step 2: Ensure that all relevant resources have an internal identifier
        $triples = $controller->generateIDs();
        $controller->insertTriples($graph, $triples);

        // Step 3: Translate properties from known vocabularies to vCard
        $triples = $reasoner->inferProperties(Constants::RULES_PROPERTIES);
        $controller->insertTriples($graph, $triples);
    }
}

<?php

namespace NielsHoppe\AWTP;

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
        'http://localhost:8080/examples/rdfa.html'
    ];

    public function __construct ($config) {

        $this->config = Config::getInstance();
        $this->config->setAll($config);

        $arcConfig = $this->config->getAll([
            "db_host", "db_name", "db_user", "db_pwd"
        ]);
        $arcConfig["sem_html_formats"] = "rdfa microformats erdf openid dc";

        $this->store = \ARC2::getStore($arcConfig);
    }

    public function exec () {

        foreach ($this->sources as $source) {

            $this->store->query("LOAD <$source>");
        }

        $reasoner = new Reasoner($this->store);
        $triples = $reasoner->inferTypes(Reasoner::RULES_TYPES);

        var_dump($triples);
    }
}

<?php

namespace NielsHoppe\RDFDAV\Console;

use NielsHoppe\RDFDAV\Config;
use NielsHoppe\RDFDAV\ARC\StoreController;
use NielsHoppe\RDFDAV\SPARQL\Reasoner;
use Sabre\DAV;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends Command {

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

    public function __construct () {

        $config = json_decode(file_get_contents('../public/config.json'), true);

        $this->config = Config::getInstance();
        $this->config->setAll($config);

        $arcConfig = $this->config->getAll([
            "db_host", "db_name", "db_user", "db_pwd"
        ]);
        $arcConfig["store_name"] = "test";
        $arcConfig["sem_html_formats"] = "rdfa microformats erdf openid dc";

        $this->store = \ARC2::getStore($arcConfig);
    }

    protected function configure () {

        $this
        // the name of the command (the part after "bin/console")
        ->setName('import')

        // the short description shown while running "php bin/console list"
        ->setDescription('Import data')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp("This command allows you to import data")
    ;
    }

    protected function execute (InputInterface $input, OutputInterface $output) {

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

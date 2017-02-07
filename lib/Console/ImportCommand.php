<?php

namespace NielsHoppe\RDFDAV\Console;

use NielsHoppe\RDFDAV\Importer;
use NielsHoppe\RDFDAV\ARC\StoreManager;
use NielsHoppe\RDFDAV\Console\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends AbstractCommand {

    protected function configure () {

        $this
            ->setName('import')
            ->setDescription('Import data')
            ->setHelp("This command allows you to import data");
    }

    protected function execute (InputInterface $input, OutputInterface $output) {

        $this->setup($input, $output);

        $config = json_decode(file_get_contents('../config.json'), true);

        $importer = new Importer($config, $this->log);

        $sources = [
            [
                'principal' => 'principals/admin',
                //'url' => 'http://localhost:8080/example.html',
                'url' => 'file:///vagrant/public/example.html',
                'refresh_interval' => 0,
                'last_accessed' => 0
            ]
        ];

        $this->log->info('Loading sources...');

        #$storemanager = new StoreManager($pdo, $config);

        foreach ($sources as $source) {

            #$store = $storemanager->getStore($source['principal']);
            #$importer->load($store, $source['url'], $graph);
            $importer->load($source['url']);
        }

        $this->log->info('Done.');

        $importer->process();

        $this->log->info('Import finished.');
    }
}

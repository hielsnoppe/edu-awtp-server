<?php

namespace NielsHoppe\RDFDAV\Console\Command;

use NielsHoppe\RDFDAV\Importer;
use NielsHoppe\RDFDAV\ARC\StoreManager;
use NielsHoppe\RDFDAV\Console\Command\AbstractCommand;
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

        $importer = new Importer($this->log);

        $sources = [
            [
                'principal' => 'principals/admin',
                'url' => 'http://localhost/examples/index.html',
                'refresh_interval' => 0,
                'last_accessed' => 0
            ]/*,
            [
                'principal' => 'principals/admin',
                'url' => 'file:///var/www/data/addressbook.rdf',
                'refresh_interval' => 0,
                'last_accessed' => 0
            ]*/
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

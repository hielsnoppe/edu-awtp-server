<?php

namespace NielsHoppe\RDFDAV\Console;

use NielsHoppe\RDFDAV\Config;
use NielsHoppe\RDFDAV\ARC\StoreManager;
use NielsHoppe\RDFDAV\Console\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StoreCommand extends AbstractCommand {

    protected function configure () {

        $this
            ->setName('store')
            ->setDescription('Manages a store')
            ->setHelp("This command helps you with the stores");

        $this->addArgument('action', InputArgument::REQUIRED, 'What do you want to do?');
        $this->addOption('store', null, InputOption::VALUE_REQUIRED, 'Which store do you want to act on?', 'test');
        $this->addOption('url', null, InputOption::VALUE_REQUIRED, 'Which store do you want to act on?');
    }

    protected function execute (InputInterface $input, OutputInterface $output) {

        $this->setup($input, $output);

        $arcConfig = $this->config->getAll([
            "db_host", "db_name", "db_user", "db_pwd"
        ]);
        $arcConfig["store_name"] = $input->getOption('store');

        $store = \ARC2::getStore($arcConfig);
        $action = $input->getArgument('action');

        switch ($action) {

            case 'setup':
            if (!$store->isSetUp()) { $store->setUp(); }
            break;

            case 'reset':
            $store->reset();
            break;

            case 'dump':
            $store->dump();
            break;

            case 'load':
            $this->load($store, $input->getOption('url'));
            break;

            default:
            $this->io->error('No such action `' . $action . '`.');
            return;
        }

        $this->log->info('Done.');
    }

    private function load ($store, $url) {

        if (is_null($url)) {

            $this->io->error('The "--url" option is required, but missing.');
            return;
        }

        $store->query('LOAD <' . $url . '>');
    }
}

<?php

namespace NielsHoppe\RDFDAV\Console;

use NielsHoppe\RDFDAV\Config;
use NielsHoppe\RDFDAV\Importer;
use NielsHoppe\RDFDAV\ARC\StoreManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCommand extends Command {

    /**
     * @var
     */
    protected $config;
    /**
     * @var LoggerInterface
     */
    protected $log;
    /**
     * @var
     */
    protected $io;

    #protected abstract function configure ();
    #protected abstract function execute (InputInterface $input, OutputInterface $output);

    protected function setup (InputInterface $input, OutputInterface $output) {

        $this->io = new SymfonyStyle($input, $output);
        $this->log = new ConsoleLogger($output);
        $this->config = Config::getInstance();

        $config = json_decode(file_get_contents('../config.json'), true);
        $this->config->setAll($config);
    }
}

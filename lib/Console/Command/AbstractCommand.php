<?php

namespace NielsHoppe\RDFDAV\Console\Command;

use NielsHoppe\RDFDAV\Config;
use NielsHoppe\RDFDAV\Console\Style\AppStyle;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command {

    /**
     * @var LoggerInterface
     */
    protected $log;
    /**
     * @var
     */
    protected $io;

    protected function setup (InputInterface $input, OutputInterface $output) {

        $this->io = new AppStyle($input, $output);
        $this->log = new ConsoleLogger($output);
        Config::getInstance()->setFromFile();
    }
}

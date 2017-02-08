#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use NielsHoppe\RDFDAV\Console\Command\ImportCommand;
use NielsHoppe\RDFDAV\Console\Command\SetupCommand;
use NielsHoppe\RDFDAV\Console\Command\StoreCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$application->addCommands([
    new ImportCommand(),
    new SetupCommand(),
    new StoreCommand(),
]);

$application->run();

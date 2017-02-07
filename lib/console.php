#!/usr/bin/env php
<?php

require '../vendor/autoload.php';

use NielsHoppe\RDFDAV\Console\ImportCommand;
use NielsHoppe\RDFDAV\Console\SetupCommand;
use NielsHoppe\RDFDAV\Console\StoreCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$application->addCommands([
    new ImportCommand(),
    new SetupCommand(),
    new StoreCommand(),
]);

$application->run();

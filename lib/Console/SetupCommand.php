<?php

namespace NielsHoppe\RDFDAV\Console;

use NielsHoppe\RDFDAV\Config;
use NielsHoppe\RDFDAV\Importer;
use NielsHoppe\RDFDAV\ARC\StoreManager;
use NielsHoppe\RDFDAV\Console\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends AbstractCommand {

    protected function configure () {

        $this
            ->setName('setup')
            ->setDescription('Setup application')
            ->setHelp("This command helps you with the setup");
    }

    protected function execute (InputInterface $input, OutputInterface $output) {

        $this->setup($input, $output);

        if (!file_exists('../config.json')) {

            $this->log->warning('No configuration file found!');
            $config = $this->setupConfig();
        }

        $config = json_decode(file_get_contents('../config.json'), true);

        if (!is_array($config)) {

            $this->log->warning('Configuration file could not be read.');
            $config = $this->setupConfig();
        }

        if (!is_array($config)) {

            $this->log->info('Finished doing nothing.');
            return;
        }

        $this->config->setAll($config);

        $this->log->info('Configuration found and read.');

        $this->setupDatabase();

        $this->log->info('Setup completed.');
    }

    private function setupConfig () {

        if (!$this->io->confirm('Do you want to create a config.json now?', false)) {

            return false;
        }

        $config = [];

        // TODO Ask all required fields
        // TODO Write to config.json
        return $config;
    }

    /**
     * @see http://sabre.io/dav/caldav/#mysql
     */
    private function setupDatabase () {

        if (!$this->io->confirm('Do you want to create the required database tables now?', false)) {

            return false;
        }

        $pdo = new \PDO(sprintf("mysql:host=%s;dbname=%s",
            $this->config->get("db_host"),
            $this->config->get("db_name")),
            $this->config->get("db_user"),
            $this->config->get("db_pwd")
        );
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $files = glob('../vendor/sabre/dav/examples/sql/mysql.*.sql');
        $files[] = '../res/sql/setup.sql';

        foreach ($files as $file) {

            $success = $pdo->query(file_get_contents($file));

            if ($success) {

                $this->log->info('Success running ' . $file);
            }
            else {

                $this->log->error('Error running ' . $file, [
                    'errorCode' => $pdo->errorCode(),
                    'errorInfo' => $pdo->errorInfo()
                ]);
            }
        }
    }
}

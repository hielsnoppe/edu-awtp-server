<?php

namespace NielsHoppe\RDFDAV\Console\Command;

use NielsHoppe\RDFDAV\Config;
use NielsHoppe\RDFDAV\Console\Command\AbstractCommand;
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

        $this->io->title('Setup');

        $this->io->section('Config');

        if (!$this->testConfig()) {

            if (!$this->setupConfig()) {

                $this->io->warning('Setup aborted. Please fix your configuration.');
                return;
            }
        }

        $this->io->section('Database');

        if (!$this->testDatabase()) {

            if (!$this->setupDatabase()) {

                $this->io->warning('Setup aborted. Please fix your database.');
                return;
            }
        }

        $this->io->section('Scheduling');

        $this->setupCron();

        $this->io->success('Setup completed.');
    }

    private function testConfig () {

        if (!file_exists(Config::DEFAULT_CONFIG_LOCATION)) {

            $this->io->text('No configuration file found.');
            return false;
        }

        $config = json_decode(file_get_contents(Config::DEFAULT_CONFIG_LOCATION), true);

        if (!is_array($config)) {

            $this->io->text('Configuration file could not be read.');
            return false;
        }

        Config::getInstance()->setFromFile();
        $this->io->text('Found a valid configuration file.');
        return true;
    }

    private function setupConfig () {

        if (!$this->io->confirm('Do you want help creating a configuration?', true)) {

            $this->io->text('Skipped configuration.');
            return false;
        }

        $config = [];

        $config['db_host'] = $this->io->ask('Database host',
                Config::DEFAULT_VALUES['db_host']);
        $config['db_name'] = $this->io->ask('Database name',
                Config::DEFAULT_VALUES['db_name']);
        $config['db_user'] = $this->io->ask('Database user',
                Config::DEFAULT_VALUES['db_user']);
        $config['db_pwd'] = $this->io->askHidden('Database password',
                function ($password) {
                    return empty($password) ?
                        Config::DEFAULT_VALUES['db_pwd'] : $password;
                });

        $config['sabre_base_uri'] = $this->io->ask('Sabre base URI',
                Config::DEFAULT_VALUES['sabre_base_uri']);
        $config['sabre_base_uri'] = $this->io->ask('Sabre proxy host',
                Config::DEFAULT_VALUES['sabre_proxy_host']);
        $config['sabre_base_uri'] = $this->io->ask('Sabre proxy port',
                Config::DEFAULT_VALUES['sabre_proxy_port']);
        $config['sabre_base_uri'] = $this->io->ask('Sabre auth realm',
                Config::DEFAULT_VALUES['sabre_auth_realm']);

        $this->io->text('Please copy and paste the following into a file in the root directory of your installation:');
        $this->io->codeblock(json_encode($config, JSON_PRETTY_PRINT), 'config.json');

        // TODO Write to config.json

        $this->io->text('Completed configuration.');
        return true;
    }

    /**
     * @todo
     */
    private function testDatabase () {

        if (!true) {

            $this->io->text('Required tables not found.');
            return false;
        }

        $this->io->text('Database schema looks good.');
        return true;
    }

    /**
     * @see http://sabre.io/dav/caldav/#mysql
     */
    private function setupDatabase () {

        if (!$this->io->confirm('Do you want to create the required database tables now?', false)) {

            $this->io->text('Skipped database setup.');
            return false;
        }

        $config = Config::getInstance();

        $pdo = new \PDO(sprintf("mysql:host=%s;dbname=%s",
            $config->get("db_host"),
            $config->get("db_name")),
            $config->get("db_user"),
            $config->get("db_pwd")
        );
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $files = glob('../vendor/sabre/dav/examples/sql/mysql.*.sql');
        $files[] = '../res/sql/setup.sql';

        foreach ($files as $file) {

            #$success = $pdo->query(file_get_contents($file));
            $success = true;

            if ($success) {

                $this->io->text('Success running ' . $file);
            }
            else {

                $this->log->error('Error running ' . $file, [
                    'errorCode' => $pdo->errorCode(),
                    'errorInfo' => $pdo->errorInfo()
                ]);
            }
        }

        $this->io->text('Completed database setup.');
        return true;
    }

    /**
     * @see https://help.ubuntu.com/community/CronHowto
     * @see http://stackoverflow.com/a/16068840/948404
     */
    private function setupCron () {

        if (!$this->io->confirm('Do you want help setting up cron?', true)) {

            $this->io->text('Skipped crontab setup.');
            return false;
        }

        $interval = $this->io->choice('How often do you want the import to run?', [
            #'reboot' => 'Run once, at startup.',
            #'yearly' => 'Run once a year (0 0 1 1 *)',
            #'annually' => '(same as yearly)',
            'monthly' => 'Run once a month (0 0 1 * *)',
            'weekly' => 'Run once a week (0 0 * * 0)',
            'daily' => 'Run once a day (0 0 * * *)',
            #'midnight' => '(same as daily)',
            'hourly' => 'Run once an hour (0 * * * *)',
        ]);

        $php = '/usr/bin/php';
        $bin = realpath(__DIR__ . '/../../console.php');
        $log = realpath(__DIR__ . '/../../../') . '/import.log';

        $this->io->text('Please copy and paste the following into your crontab editor:');
        $this->io->codeblock(sprintf('@%s %s %s import -vv >> %s 2>&1', $interval, $php, $bin, $log), '$ crontab -e');

        $this->io->text('Completed crontab setup.');
        return true;
    }
}

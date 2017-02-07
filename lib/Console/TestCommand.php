<?php

namespace NielsHoppe\RDFDAV\Console;

use NielsHoppe\RDFDAV\ARC\StoreManager;
use NielsHoppe\RDFDAV\CardDAV\VCardBuilder;
use NielsHoppe\RDFDAV\Config;
use NielsHoppe\RDFDAV\Constants;
use NielsHoppe\RDFDAV\Importer;
use NielsHoppe\RDFDAV\SPARQL\MappingQueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class TestCommand extends AbstractCommand {

    protected function configure () {

        $this
            ->setName('test')
            ->setDescription('Trying out stuff')
            ->setHelp("This command does things.");
    }

    protected function execute (InputInterface $input, OutputInterface $output) {

        $this->setup($input, $output);

        $this->log->emergency('Test');
        $this->log->alert('Test');
        $this->log->critical('Test');
        $this->log->error('Test');
        $this->log->warning('Test');
        $this->log->notice('Test');
        $this->log->info('Test');
        $this->log->debug('Test');

        $builder = new MappingQueryBuilder([
            "vcard" => [
                "vcard" => [
                    "fn" => "fn",
                    "given-name" => "given-name",
                    "family-name" => "family-name",
                    "nick" => "nick",
                    "hasEmail" => "hasEmail"
                ]
            ],
            "foaf" => [
                "vcard" => [
                    "name" => "fn",
                    "givenname" => "given-name",
                    "family_name" => "family-name",
                    "firstname" => "given-name",
                    "lastname" => "family-name",
                    "nick" => "nickname",
                    "homepage" => "hasURL",
                    "phone" => "hasTelephone"
                ]
            ],
            "bio" => [
                "bio" => [
                    "date" => "date"
                ]
            ]
        ]);

        #echo($builder->getQuery("vcard:Individual", "vcard:VCard"));

        #$rs = $store->query(Constants::SPARQL_ALL_DATA);
        #var_dump($rs);

        //$config = array('auto_extract' => 0);
        $parser = \ARC2::getSemHTMLParser();
        $parser->parse('http://localhost:8080/examples/rdfa.html');
        $parser->extractRDF('rdfa');

        $triples = $parser->getTriples();
        $n3 = $parser->toNTriples($triples);
        echo($n3);
    }
}

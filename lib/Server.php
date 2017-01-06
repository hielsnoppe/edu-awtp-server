<?php

namespace NielsHoppe\AWTP\Server;

use Sabre\DAV;
use Sabre\DAVACL;

class Server {

    /**
     * @var \ARC_Store
     */
    private $arcStore;
    /**
     * @var \Sabre\DAV\Server
     */
    private $sabreServer;

    public function __construct ($config) {

        $pdo = new \PDO(sprintf("mysql:host=%s;dbname=%s",
            $config["db_host"], $config["db_name"]),
            $config["db_user"], $config["db_pwd"]
        );
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        //Mapping PHP errors to exceptions
        function exception_error_handler($errno, $errstr, $errfile, $errline ) {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        }
        set_error_handler("exception_error_handler");

        $arcConfig = array(
            /* db */
            'db_host' => $config["db_host"], /* default: localhost */
            'db_name' => $config["db_name"],
            'db_user' => $config["db_user"],
            'db_pwd' => $config["db_pwd"],
            /* store */
            'store_name' => $config["store_name"],
            /* network */
            'proxy_host' => $config["proxy_host"],
            'proxy_port' => $config["proxy_port"],
            /* parsers */
            'bnode_prefix' => 'bn',
            /* sem html extraction */
            'sem_html_formats' => 'rdfa microformats',
        );

        $this->arcStore = \ARC2::getStore($arcConfig);

        // Backends
        $authBackend = new DAV\Auth\Backend\PDO($pdo);
        $principalBackend = new DAVACL\PrincipalBackend\PDO($pdo);
        #$calendarBackend = new CalDAV\Backend\ARC($this->arcStore);
        $carddavBackend = new CardDAV\Backend\ARC($this->arcStore);

        // Directory tree
        $tree = array(
            new DAVACL\PrincipalCollection($principalBackend),
            #new CalDAV\CalendarRoot($principalBackend, $calendarBackend),
            new CardDAV\AddressBookRoot($principalBackend, $carddavBackend)
        );

        // The object tree needs in turn to be passed to the server class
        $this->sabreServer = new DAV\Server($tree);

        // You are highly encouraged to set your WebDAV server base url. Without it,
        // SabreDAV will guess, but the guess is not always correct. Putting the
        // server on the root of the domain will improve compatibility.
        $this->sabreServer->setBaseUri($config["base_uri"]);

        // Authentication plugin
        $this->sabreServer->addPlugin(new DAV\Auth\Plugin($authBackend, $config["auth_realm"]));

        // CalDAV plugin
        #$this->sabreServer->addPlugin(new CalDAV\Plugin());

        // ACL plugin
        $this->sabreServer->addPlugin(new DAVACL\Plugin());

        // Support for html frontend
        $this->sabreServer->addPlugin(new DAV\Browser\Plugin());
    }

    public function exec() {

        // And off we go!
        $this->sabreServer->exec();
    }
}

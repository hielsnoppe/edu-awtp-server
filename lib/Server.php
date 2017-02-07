<?php

namespace NielsHoppe\RDFDAV;

use Sabre\DAV;

class Server {

    /**
     * @var Config
     */
    private $config;
    /**
     * @var \Sabre\DAV\Server
     */
    private $sabre;

    public function __construct ($config) {

        $this->config = Config::getInstance();
        $this->config->setAll($config);

        $pdo = new \PDO(sprintf("mysql:host=%s;dbname=%s",
            $this->config->get("db_host"),
            $this->config->get("db_name")),
            $this->config->get("db_user"),
            $this->config->get("db_pwd")
        );
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $arcConfig = $this->config->getAll([
            "db_host", "db_name", "db_user", "db_pwd"
        ]);

        // Backends
        $authBackend = new \Sabre\DAV\Auth\Backend\PDO($pdo);
        $principalBackend = new \Sabre\DAVACL\PrincipalBackend\PDO($pdo);
        #$calendarBackend = new CalDAV\Backend\ARC($this->arc);
        $carddavBackend = new CardDAV\Backend\ARC($pdo, $arcConfig);

        // Directory tree
        $tree = array(
            new \Sabre\DAVACL\PrincipalCollection($principalBackend),
            #new CalDAV\CalendarRoot($principalBackend, $calendarBackend),
            new \Sabre\CardDAV\AddressBookRoot($principalBackend, $carddavBackend)
        );

        // The object tree needs in turn to be passed to the server class
        $this->sabre = new \Sabre\DAV\Server($tree);

        // You are highly encouraged to set your WebDAV server base url. Without it,
        // SabreDAV will guess, but the guess is not always correct. Putting the
        // server on the root of the domain will improve compatibility.
        $this->sabre->setBaseUri($this->config->get("sabre_base_uri"));

        // Authentication plugin
        $this->sabre->addPlugin(new \Sabre\DAV\Auth\Plugin($authBackend,
                $this->config->get("sabre_auth_realm")));

        // CalDAV plugin
        #$this->sabreServer->addPlugin(new CalDAV\Plugin());

        // ACL plugin
        $this->sabre->addPlugin(new \Sabre\DAVACL\Plugin());

        // Support for html frontend
        $this->sabre->addPlugin(new \Sabre\DAV\Browser\Plugin());
    }

    public function exec() {

        $this->sabre->exec();
    }
}

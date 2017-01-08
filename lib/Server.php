<?php

namespace NielsHoppe\AWTP\Server;

use Sabre\DAV;

function array_extract($array, $keys) {
    return array_intersect_key($array, array_flip($keys));
}

class Server {

    /**
     * @var array
     */
    private $arcCconfig;
    /**
     * @var \Sabre\DAV\Server
     */
    private $sabre;

    public function __construct ($config) {

        $pdo = new \PDO(sprintf("mysql:host=%s;dbname=%s",
            $config["db_host"], $config["db_name"]),
            $config["db_user"], $config["db_pwd"]
        );
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $arcConfig = array_extract($config, ["db_host", "db_name", "db_user", "db_pwd"]);

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
        $this->sabre->setBaseUri($config["base_uri"]);

        // Authentication plugin
        $this->sabre->addPlugin(new \Sabre\DAV\Auth\Plugin($authBackend, $config["auth_realm"]));

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

<?php
use Sabre\DAV;
use Sabre\CalDAV;
use Sabre\CardDAV;
use Sabre\DAVACL;

$pdo = new \PDO('sqlite:../data/db.sqlite');
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

//Mapping PHP errors to exceptions
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");

// Files we need
require_once '../vendor/autoload.php';

// Backends
$authBackend = new DAV\Auth\Backend\PDO($pdo);
$principalBackend = new DAVACL\PrincipalBackend\PDO($pdo);
$calendarBackend = new CalDAV\Backend\PDO($pdo);
$carddavBackend = new CardDAV\Backend\PDO($pdo);

// Directory tree
$tree = array(
    new DAVACL\PrincipalCollection($principalBackend),
    new CalDAV\CalendarRoot($principalBackend, $calendarBackend),
    new CardDAV\AddressBookRoot($principalBackend, $carddavBackend)
);


// The object tree needs in turn to be passed to the server class
$server = new DAV\Server($tree);

// You are highly encouraged to set your WebDAV server base url. Without it,
// SabreDAV will guess, but the guess is not always correct. Putting the
// server on the root of the domain will improve compatibility.
$server->setBaseUri('/');

// Authentication plugin
$authPlugin = new DAV\Auth\Plugin($authBackend,'SabreDAV');
$server->addPlugin($authPlugin);

// CalDAV plugin
$caldavPlugin = new CalDAV\Plugin();
$server->addPlugin($caldavPlugin);

// ACL plugin
$aclPlugin = new DAVACL\Plugin();
$server->addPlugin($aclPlugin);

// Support for html frontend
$browser = new DAV\Browser\Plugin();
$server->addPlugin($browser);

// And off we go!
$server->exec();

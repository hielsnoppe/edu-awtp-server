<?php

namespace NielsHoppe\AWTP\CardDAV\Backend;

use Util_TestDox_NamePrettifierTest;
use Asparagus\QueryBuilder;
use NielsHoppe\AWTP\ARC\StoreController;
use NielsHoppe\AWTP\CardDAV\VCardBuilder;
use NielsHoppe\AWTP\Constants;
use NielsHoppe\AWTP\SPARQL\MappingQueryBuilder;
use Sabre\CardDAV;
use Sabre\CardDAV\Backend\PDO;
use Sabre\CardDAV\Backend\SyncSupport;
use Sabre\CardDAV\Backend\AbstractBackend;
use Sabre\DAV;

/**
 * ARC2 CardDAV backend
 *
 * This CardDAV backend uses ARC2 to store addressbooks
 *
 * @copyright Copyright (C) Niels Hoppe (http://nielshoppe.de/)
 * @author Niels Hoppe (http://nielshoppe.de/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class ARC extends PDO implements SyncSupport {

    /**
     * ARC configuration
     *
     * @var mixed[]
     */
    protected $config;

    /**
     * The PDO table name used to store ARC2 stores for addressbooks
     */
    public $addressBooksStoresTableName = 'addressbooks_arcstores';

    /**
     * @var mixed[]
     */
    public static $queryPrefixes = array(
        "rdf" => Constants::NS_RDF,
        "rdfs" => Constants::NS_RDFS,

        "vcard" => Constants::NS_VCARD,
        "foaf" => Constants::NS_FOAF,
        "bio" => Constants::NS_BIO,

        "app" => Constants::NS_APP
    );

    public static $mappings = [
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
                "nick" => "nickname",
                "homepage" => "hasURL",
                "phone" => "hasTelephone"
            ]
        ]
    ];

    /**
     * Sets up the object
     *
     * @param \PDO $pdo
     * @param array $config Configuration for ARC2
     */
    function __construct($pdo, array $config) {

        parent::__construct($pdo);

        $this->config = $config;
    }

    private function getStoreForAddressBook($addressbookId) {

        /* TODO:
        1. Get principal uri for addressbook
        2. Get store name for principal
         */
        /*
        $stmt = $this->pdo->prepare("SELECT storename FROM " .
                $this->addressBooksStoresTableName .
                " WHERE addressbookid = ?");
        $stmt->execute([$addressbookId]);

        if ($stmt->rowCount()) {

            $stmt->bindColumn("storename", $storeName);
            $stmt->fetch(\PDO::FETCH_BOUND);
        }
        else {

            // TODO Create entry in table
        }
        */
        $storeName = "addressbook_" . strval($addressbookId);
        $storeName = "test"; // XXX Override for testing

        $config = array(
            "db_host" => $this->config["db_host"],
            "db_name" => $this->config["db_name"],
            "db_user" => $this->config["db_user"],
            "db_pwd" => $this->config["db_pwd"],
            "store_name" => $storeName
        );

        $store = \ARC2::getStore($config);

        return $store;
    }

    /**
     * Returns the list of addressbooks for a specific user.
     *
     * @param string $principalUri
     * @return array
     */
     /*
    function getAddressBooksForUser($principalUri) {

        return parent::getAddressBooksForUser($principalUri);
    }
    */


    /**
     * Updates properties for an address book.
     *
     * The list of mutations is stored in a Sabre\DAV\PropPatch object.
     * To do the actual updates, you must tell this object which properties
     * you're going to process with the handle() method.
     *
     * Calling the handle method is like telling the PropPatch object "I
     * promise I can handle updating this property".
     *
     * Read the PropPatch documenation for more info and examples.
     *
     * @param string $addressbookId
     * @param \Sabre\DAV\PropPatch $propPatch
     * @return void
     */
     /*
    function updateAddressBook($addressbookId, \Sabre\DAV\PropPatch $propPatch) {

        return parent::updateAddressBook($addressbookId, $propPatch);
    }
    */

    // BEGIN

    /**
     * Creates a new address book
     *
     * @param string $principalUri
     * @param string $url Just the 'basename' of the url.
     * @param array $properties
     * @return int Last insert id
     */
    function createAddressBook($principalUri, $url, array $properties) {

        $addressbookId = parent::createAddressBook($principalUri, $url, $properties);
        $store = $this->getStoreForAddressBook($addressbookId);

        if (!$store->isSetUp()) $store->setUp();

        return $addressbookId;
    }

    /**
     * Deletes an entire addressbook and all its contents
     *
     * @param int $addressbookId
     * @return void
     */
    function deleteAddressBook($addressbookId) {

        $store = $this->getStoreForAddressBook($addressbookId);
        $store->drop();

        parent::deleteAddressBook($addressbookId);
    }

    /**
     * Returns all cards for a specific addressbook id.
     *
     * This method should return the following properties for each card:
     *   * carddata - raw vcard data
     *   * uri - Some unique url
     *   * lastmodified - A unix timestamp
     *
     * It's recommended to also return the following properties:
     *   * etag - A unique etag. This must change every time the card changes.
     *   * size - The size of the card in bytes.
     *
     * If these last two properties are provided, less time will be spent
     * calculating them. If they are specified, you can also ommit carddata.
     * This may speed up certain requests, especially with large cards.
     *
     * @param mixed $addressbookId
     * @return array
     */
    function getCards($addressbookId) {

        $store = $this->getStoreForAddressBook($addressbookId);
        $controller = new StoreController($store);

        /*
        $builder = new MappingQueryBuilder(self::$mappings);

        $rs = $store->query($builder->getQuery("foaf:Person", "vcard:VCard"));
        $result = [];
        */
        $rs = $controller->getCards();

        foreach ($rs["result"] as $uri => $data) {

            $builder = new VCardBuilder();
            $builder->readFromRDF($data);

            $uri = 'ERROR';
            if (array_key_exists(Constants::NS_APP . 'id', $data)) {

                $uri = $data[Constants::NS_APP . 'id'][0]['value'];
            }

            // id, uri, lastmodified, etag, size, carddata
            $result[] = [
                'uri' => $uri,
                'carddata' => $builder->getCard()->serialize()
            ];
        }

        /*
        $stmt = $this->pdo->prepare('SELECT id, uri, lastmodified, etag, size FROM ' . $this->cardsTableName . ' WHERE addressbookid = ?');
        $stmt->execute([$addressbookId]);

        $result = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['etag'] = '"' . $row['etag'] . '"';
            $row['lastmodified'] = (int)$row['lastmodified'];
            $result[] = $row;
        }
        */
        return $result;
    }

    /**
     * Returns a specfic card.
     *
     * The same set of properties must be returned as with getCards. The only
     * exception is that 'carddata' is absolutely required.
     *
     * If the card does not exist, you must return false.
     *
     * @param mixed $addressbookId
     * @param string $cardUri
     * @return array
     */
    function getCard($addressbookId, $cardUri) {

        $store = $this->getStoreForAddressbook($addressbookId);
        $controller = new StoreController($store);

        $rs = $controller->getCard($cardUri);
        #var_dump($rs); exit();

        foreach ($rs["result"] as $uri => $data) {

            $builder = new VCardBuilder();
            $builder->readFromRDF($data);

            // id, uri, lastmodified, etag, size, carddata
            $result = [
                "uri" => md5($uri),
                "carddata" => $builder->getCard()->serialize()
            ];
        }

        /*
        $stmt = $this->pdo->prepare('SELECT id, carddata, uri, lastmodified, etag, size FROM ' . $this->cardsTableName . ' WHERE addressbookid = ? AND uri = ? LIMIT 1');
        $stmt->execute([$addressbookId, $cardUri]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) return false;

        $result['etag'] = '"' . $result['etag'] . '"';
        $result['lastmodified'] = (int)$result['lastmodified'];
        return $result;
        */

        return $result;
    }

    /**
     * Returns a list of cards.
     *
     * This method should work identical to getCard, but instead return all the
     * cards in the list as an array.
     *
     * If the backend supports this, it may allow for some speed-ups.
     *
     * @param mixed $addressbookId
     * @param array $uris
     * @return array
     */
    function getMultipleCards($addressbookId, array $uris) {

        $query = 'SELECT id, uri, lastmodified, etag, size, carddata FROM ' . $this->cardsTableName . ' WHERE addressbookid = ? AND uri IN (';
        // Inserting a whole bunch of question marks
        $query .= implode(',', array_fill(0, count($uris), '?'));
        $query .= ')';

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_merge([$addressbookId], $uris));
        $result = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['etag'] = '"' . $row['etag'] . '"';
            $row['lastmodified'] = (int)$row['lastmodified'];
            $result[] = $row;
        }
        return $result;

    }

    /**
     * Creates a new card.
     *
     * The addressbook id will be passed as the first argument. This is the
     * same id as it is returned from the getAddressBooksForUser method.
     *
     * The cardUri is a base uri, and doesn't include the full path. The
     * cardData argument is the vcard body, and is passed as a string.
     *
     * It is possible to return an ETag from this method. This ETag is for the
     * newly created resource, and must be enclosed with double quotes (that
     * is, the string itself must contain the double quotes).
     *
     * You should only return the ETag if you store the carddata as-is. If a
     * subsequent GET request on the same card does not have the same body,
     * byte-by-byte and you did return an ETag here, clients tend to get
     * confused.
     *
     * If you don't return an ETag, you can just return null.
     *
     * @param mixed $addressbookId
     * @param string $cardUri
     * @param string $cardData
     * @return string|null
     */
    function createCard($addressbookId, $cardUri, $cardData) {

        $stmt = $this->pdo->prepare('INSERT INTO ' . $this->cardsTableName . ' (carddata, uri, lastmodified, addressbookid, size, etag) VALUES (?, ?, ?, ?, ?, ?)');

        $etag = md5($cardData);

        $stmt->execute([
            $cardData,
            $cardUri,
            time(),
            $addressbookId,
            strlen($cardData),
            $etag,
        ]);

        $this->addChange($addressbookId, $cardUri, 1);

        return '"' . $etag . '"';

    }

    /**
     * Updates a card.
     *
     * The addressbook id will be passed as the first argument. This is the
     * same id as it is returned from the getAddressBooksForUser method.
     *
     * The cardUri is a base uri, and doesn't include the full path. The
     * cardData argument is the vcard body, and is passed as a string.
     *
     * It is possible to return an ETag from this method. This ETag should
     * match that of the updated resource, and must be enclosed with double
     * quotes (that is: the string itself must contain the actual quotes).
     *
     * You should only return the ETag if you store the carddata as-is. If a
     * subsequent GET request on the same card does not have the same body,
     * byte-by-byte and you did return an ETag here, clients tend to get
     * confused.
     *
     * If you don't return an ETag, you can just return null.
     *
     * @param mixed $addressbookId
     * @param string $cardUri
     * @param string $cardData
     * @return string|null
     */
    function updateCard($addressbookId, $cardUri, $cardData) {

        $stmt = $this->pdo->prepare('UPDATE ' . $this->cardsTableName . ' SET carddata = ?, lastmodified = ?, size = ?, etag = ? WHERE uri = ? AND addressbookid =?');

        $etag = md5($cardData);
        $stmt->execute([
            $cardData,
            time(),
            strlen($cardData),
            $etag,
            $cardUri,
            $addressbookId
        ]);

        $this->addChange($addressbookId, $cardUri, 2);

        return '"' . $etag . '"';

    }

    /**
     * Deletes a card
     *
     * @param mixed $addressbookId
     * @param string $cardUri
     * @return bool
     */
    function deleteCard($addressbookId, $cardUri) {

        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->cardsTableName . ' WHERE addressbookid = ? AND uri = ?');
        $stmt->execute([$addressbookId, $cardUri]);

        $this->addChange($addressbookId, $cardUri, 3);

        return $stmt->rowCount() === 1;

    }

    // END

    /**
     * The getChanges method returns all the changes that have happened, since
     * the specified syncToken in the specified address book.
     *
     * This function should return an array, such as the following:
     *
     * [
     *   'syncToken' => 'The current synctoken',
     *   'added'   => [
     *      'new.txt',
     *   ],
     *   'modified'   => [
     *      'updated.txt',
     *   ],
     *   'deleted' => [
     *      'foo.php.bak',
     *      'old.txt'
     *   ]
     * ];
     *
     * The returned syncToken property should reflect the *current* syncToken
     * of the addressbook, as reported in the {http://sabredav.org/ns}sync-token
     * property. This is needed here too, to ensure the operation is atomic.
     *
     * If the $syncToken argument is specified as null, this is an initial
     * sync, and all members should be reported.
     *
     * The modified property is an array of nodenames that have changed since
     * the last token.
     *
     * The deleted property is an array with nodenames, that have been deleted
     * from collection.
     *
     * The $syncLevel argument is basically the 'depth' of the report. If it's
     * 1, you only have to report changes that happened only directly in
     * immediate descendants. If it's 2, it should also include changes from
     * the nodes below the child collections. (grandchildren)
     *
     * The $limit argument allows a client to specify how many results should
     * be returned at most. If the limit is not specified, it should be treated
     * as infinite.
     *
     * If the limit (infinite or not) is higher than you're willing to return,
     * you should throw a Sabre\DAV\Exception\TooMuchMatches() exception.
     *
     * If the syncToken is expired (due to data cleanup) or unknown, you must
     * return null.
     *
     * The limit is 'suggestive'. You are free to ignore it.
     *
     * @param string $addressbookId
     * @param string $syncToken
     * @param int $syncLevel
     * @param int $limit
     * @return array
     */
     /*
    function getChangesForAddressBook($addressbookId, $syncToken, $syncLevel, $limit = null) {

        // Current synctoken
        $stmt = $this->pdo->prepare('SELECT synctoken FROM ' . $this->addressBooksTableName . ' WHERE id = ?');
        $stmt->execute([ $addressbookId ]);
        $currentToken = $stmt->fetchColumn(0);

        if (is_null($currentToken)) return null;

        $result = [
            'syncToken' => $currentToken,
            'added'     => [],
            'modified'  => [],
            'deleted'   => [],
        ];

        if ($syncToken) {

            $query = "SELECT uri, operation FROM " . $this->addressBookChangesTableName . " WHERE synctoken >= ? AND synctoken < ? AND addressbookid = ? ORDER BY synctoken";
            if ($limit > 0) $query .= " LIMIT " . (int)$limit;

            // Fetching all changes
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$syncToken, $currentToken, $addressbookId]);

            $changes = [];

            // This loop ensures that any duplicates are overwritten, only the
            // last change on a node is relevant.
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

                $changes[$row['uri']] = $row['operation'];

            }

            foreach ($changes as $uri => $operation) {

                switch ($operation) {
                    case 1:
                        $result['added'][] = $uri;
                        break;
                    case 2:
                        $result['modified'][] = $uri;
                        break;
                    case 3:
                        $result['deleted'][] = $uri;
                        break;
                }

            }
        } else {
            // No synctoken supplied, this is the initial sync.
            $query = "SELECT uri FROM " . $this->cardsTableName . " WHERE addressbookid = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$addressbookId]);

            $result['added'] = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        }
        return $result;

    }
    */

    /**
     * Adds a change record to the addressbookchanges table.
     *
     * @param mixed $addressbookId
     * @param string $objectUri
     * @param int $operation 1 = add, 2 = modify, 3 = delete
     * @return void
     */
     /*
    protected function addChange($addressbookId, $objectUri, $operation) {

        $stmt = $this->pdo->prepare('INSERT INTO ' . $this->addressBookChangesTableName . ' (uri, synctoken, addressbookid, operation) SELECT ?, synctoken, ?, ? FROM ' . $this->addressBooksTableName . ' WHERE id = ?');
        $stmt->execute([
            $objectUri,
            $addressbookId,
            $operation,
            $addressbookId
        ]);
        $stmt = $this->pdo->prepare('UPDATE ' . $this->addressBooksTableName . ' SET synctoken = synctoken + 1 WHERE id = ?');
        $stmt->execute([
            $addressbookId
        ]);

    }
    */
}

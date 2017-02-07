<?php

namespace NielsHoppe\RDFDAV\ARC;

use \ARC2_Store;
use \NielsHoppe\RDFDAV\Constants;

/**
 * @see http://stackoverflow.com/a/2940696
 */
class StoreManager {

    /**
     * @var array $config
     */
    private $config;
    /**
     * @var \PDO $pdo
     */
    private $pdo;

    /**
     * @param \PDO $pdo
     */
    public function __construct (\PDO $pdo, array $config) {

        $this->config = $config;
        $this->pdo = $pdo;
    }

    /**
     * @todo
     * @param string $principalUri
     * @return \ARC2_Store
     */
    public function getStore ($principalUri) {

        $storeName = "test"; // XXX Override for testing

        $config = array(
            "db_host" => $this->config["db_host"],
            "db_name" => $this->config["db_name"],
            "db_user" => $this->config["db_user"],
            "db_pwd" => $this->config["db_pwd"],
            "store_name" => $storeName
        );

        $store = \ARC2::getStore($config);

        if (!$store->isSetUp()) {

            $store->setUp();
        }

        return $store;
    }
}

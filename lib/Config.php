<?php

namespace NielsHoppe\RDFDAV;

class Config {

    const DEFAULT_CONFIG_LOCATION = __DIR__ . '/../config.json';

    const DEFAULT_VALUES = [
        "db_host" => "127.0.0.1",
        "db_name" => "scotchbox",
        "db_user" => "root",
        "db_pwd" => "root",

        "sabre_base_uri" => "/",
        "sabre_proxy_host" => "192.168.1.1",
        "sabre_proxy_port" => 8080,
        "sabre_auth_realm" => "edu-awtp-server"
    ];

    private $values = [];

    private static $instance;

    private function __construct () {
    }

    public static function getInstance () {

        if (is_null(self::$instance)) {

            self::$instance = new Config();
        }

        return self::$instance;
    }

    public function get ($key, $default = true) {

        if (array_key_exists($key, $this->values)) {

            return $this->values[$key];
        }
        else if ($default && array_key_exists($key, self::DEFAULT_VALUES)) {

            return self::DEFAULT_VALUES[$key];
        }
        else return null;
    }

    public function getAll (array $keys, $defaults = true) {

        if (!is_array($keys)) return array();

        $values = [];

        if ($defaults) {

            $values = Functions::array_extract(self::DEFAULT_VALUES, $keys);
        }

        $values = array_merge($values,
            Functions::array_extract($this->values, $keys)
        );

        return $values;
    }

    public function set ($key, $value) {

        $this->values[$key] = $value;
    }

    public function setAll (array $values) {

        foreach ($values as $key => $value) {

            $this->set($key, $value);
        }
    }

    /**
     */
    public function setFromFile ($filename = self::DEFAULT_CONFIG_LOCATION) {

        if (!file_exists($filename)) {

            return false;
        }

        $config = json_decode(file_get_contents($filename), true);

        if (!is_array($config)) {

            return false;
        }

        $this->setAll($config);

        return true;
    }
}

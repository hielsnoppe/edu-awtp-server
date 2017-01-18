<?php

namespace NielsHoppe\AWTP\Server;

class Config {

    private static $instance;

    private static $defaults = [
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

    private function __construct() {
    }

    public static function getInstance() {

        if (is_null(self::$instance)) {

            self::$instance = new Config();
        }

        return self::$instance;
    }

    public function get($key, $default = true) {

        if (array_key_exists($key, $this->values)) {

            return $this->values[$key];
        }
        else if ($default && array_key_exists($key, self::$defaults)) {

            return self::$defaults[$key];
        }
        else return null;
    }

    public function getAll(array $keys, $defaults = true) {

        if (!is_array($keys)) return array();

        $values = [];

        if ($defaults) {

            $values = Functions::array_extract(self::$defaults, $keys);
        }

        $values = array_merge( $values,
            Functions::array_extract($this->values, $keys)
        );

        return $values;
    }

    public function set($key, $value) {

        $this->values[$key] = $value;
    }

    public function setAll(array $values) {

        foreach ($values as $key => $value) {

            $this->set($key, $value);
        }
    }

    /**
     * @todo A lot
     */
    public function setFromFile($filename) {

        parse_ini_file($filename, true);
    }
}

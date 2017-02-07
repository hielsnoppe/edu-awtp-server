<?php

namespace NielsHoppe\RDFDAV;

class Functions {

    public static function array_extract($array, $keys) {

        return array_intersect_key($array, array_flip($keys));
    }

    public static function exception_error_handler($errno, $errstr, $errfile, $errline) {
      
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
}

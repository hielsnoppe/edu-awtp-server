<?php

namespace NielsHoppe\AWTP\Server;

class Functions {

    public static function array_extract($array, $keys) {
        
        return array_intersect_key($array, array_flip($keys));
    }
}

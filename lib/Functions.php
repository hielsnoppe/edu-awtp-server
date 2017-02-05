<?php

namespace NielsHoppe\AWTP;

class Functions {

    public static function array_extract($array, $keys) {
        
        return array_intersect_key($array, array_flip($keys));
    }
}

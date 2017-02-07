<?php

namespace NielsHoppe\RDFDAV;

class FunctionsTest extends \PHPUnit_Framework_TestCase {

    public function test_array_extract () {

        $array = [
            'foo' => 42,
            'bar' => 43,
            'baz' => 44
        ];

        $result = Functions::array_extract($array, ['bar', 'baz']);

        $this->assertEquals([
            'bar' => 43,
            'baz' => 44
        ], $result);
    }
}

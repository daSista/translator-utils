<?php

class EnvironmentTest extends PHPUnit_Framework_TestCase
{

    public function testCouchDbIsUp()
    {
        $this->assertTrue(is_resource(@fsockopen('127.0.0.1', '5984')));
    }
}

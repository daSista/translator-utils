<?php
namespace Translator\Test;

use Doctrine\CouchDB\CouchDBClient;
use Doctrine\CouchDB\HTTP\SocketClient as HttpClient;

abstract class CouchDbTestCase extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        self::db()->deleteDatabase(TEST_COUCHDB_NAME);
    }

//----------------------------------------------------------------------------------------------------------------------

    protected static function db()
    {
        return new CouchDBClient(new HttpClient(), TEST_COUCHDB_NAME);
    }
}

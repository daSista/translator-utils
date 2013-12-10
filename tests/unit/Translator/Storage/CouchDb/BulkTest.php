<?php

namespace Translator\Storage\CouchDb;

use Doctrine\CouchDB\CouchDBClient;
use Mockery as m;
use Translator\String;

class BulkTest extends \PHPUnit_Framework_TestCase
{
    public function testDoesntDoSingleRequestToCouchDb()
    {
        $connection = m::mock('Doctrine\\CouchDB\\HTTP\\Client');
        $connection->shouldReceive('request')->never();

        self::storage(self::couchDb($connection))->ensurePresence(self::yesString());
    }

    public function testChecksExistingDocumentsBeforeBulkUpdate()
    {
        $query = m::mock();
        $query->shouldReceive('setKeys')->with(array(
                self::yesString()->hash(),
                self::noString()->hash(),
            ))->once();

        $storage = self::storage(m::mock('Doctrine\\CouchDB\\HTTP\\Client',
                array('createViewQuery' => $query, 'createBulkUpdater' => null)));
        $storage->ensurePresence(self::yesString());
        $storage->ensurePresence(self::noString());
        $storage->commit();
    }

    public function testBulkUpdateNewTranslations()
    {
        $this->markTestSkipped();

        $findViewResponse = array();
        $storage = self::storage(
            m::mock(
                'Doctrine\\CouchDB\\HTTP\\Client',
                array('createViewQuery' => m::mock(array('execute' => $findViewResponse)))
            )
        );

        $storage->ensurePresence(self::yesString());
        $storage->ensurePresence(self::noString());
        $storage->commit();


    }

//----------------------------------------------------------------------------------------------------------------------

    private static function storage($couch)
    {
        return new Bulk($couch, 'de_CH');
    }

    private static function couchDb($connection)
    {
        return new CouchDBClient($connection, 'fake_db_name');
    }

    private static function yesString()
    {
        return String::create('yes', 'Ja');
    }

    private static function noString()
    {
        return String::create('no', 'Nein');
    }
}
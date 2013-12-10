<?php

namespace Translator\Storage\CouchDb;

use Doctrine\CouchDB\CouchDBClient;
use Doctrine\CouchDB\HTTP\Response;
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
        $query->shouldReceive('setKeys');
        $query->shouldIgnoreMissing();

        $storage = self::storage(m::mock('Doctrine\\CouchDB\\HTTP\\Client',
                array('createViewQuery' => $query, 'createBulkUpdater' => self::noObj())));
        $storage->ensurePresence(self::yesString());
        $storage->ensurePresence(self::noString());
        $storage->commit();
    }

    public function testBulkUpdateNewTranslations()
    {
        $connection = m::mock('Doctrine\\CouchDB\\HTTP\\Client');
        $connection->shouldReceive('request')
            ->with('GET', anything())
            ->andReturn(new Response(200, array(), array('rows' => array()), true));

        $connection->shouldReceive('request')->with('POST', '/fake_db_name/_bulk_docs', m::on(function ($arg) {

                    return json_decode($arg, true) === array(
                        'docs' => array(
                            array(
                                'key' => 'yes',
                                'translation' => 'Ja',
                                'namespace' => null,
                                'source' => array(),
                            ),
                            array(
                                'key' => 'no',
                                'translation' => 'Nein',
                                'namespace' => null,
                                'source' => array(),
                            ),
                        ),
                    );
            }))->once();

        $storage = self::storage(self::couchDb($connection));
        $storage->ensurePresence(self::yesString());
        $storage->ensurePresence(self::noString());
        $storage->commit();

    }

    public function testMergeExistingStringWithNewOnUpdate()
    {
        $connection = m::mock('Doctrine\\CouchDB\\HTTP\\Client');
        $connection->shouldReceive('request')
            ->with('GET', anything())
            ->andReturn(new Response(200, array(), array('rows' => array(
                    array(
                        'value' => array(
                            '_id' => 'couchdb-internal-id',
                            '_rev' => 'couchdb-revision-id',
                            'key' => 'yes',
                            'translation' => 'Ja',
                            'namespace' => null,
                            'source' => array(),
                        ),
                    )
                )), true));

        $connection->shouldReceive('request')->with('POST', '/fake_db_name/_bulk_docs', m::on(function ($arg) {

                    return json_decode($arg, true) === array(
                        'docs' => array(
                            array(
                                'key' => 'yes',
                                'translation' => 'Ja',
                                'namespace' => null,
                                'source' => array(),
                                '_id' => 'couchdb-internal-id',
                                '_rev' => 'couchdb-revision-id',
                            ),
                            array(
                                'key' => 'no',
                                'translation' => 'Nein',
                                'namespace' => null,
                                'source' => array(),
                            ),
                        ),
                    );
                }))->once();

        $storage = self::storage(self::couchDb($connection));
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

    private static function noObj()
    {
        return m::mock('', function ($mock) {
                $mock->shouldIgnoreMissing();
                return $mock;
            });
    }
}
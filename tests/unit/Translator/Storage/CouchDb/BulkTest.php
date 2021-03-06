<?php

namespace Translator\Storage\CouchDb;

use Doctrine\CouchDB\CouchDBClient;
use Doctrine\CouchDB\HTTP\Response;
use Mockery as m;
use Translator\MultiString;

class BulkTest extends \PHPUnit_Framework_TestCase
{
    public function testDoesntDoSingleRequestToCouchDb()
    {
        $connection = m::mock('Doctrine\\CouchDB\\HTTP\\Client');
        $connection->shouldReceive('request')->never();

        self::storage(self::couchDb($connection))->ensurePresence(self::yesString());
    }

    public function testBulkUpdateNewTranslations()
    {
        $connection = m::mock('Doctrine\\CouchDB\\HTTP\\Client');
        $connection->shouldReceive('request')
            ->with('POST', matchesPattern('@^/fake_db_name/_design/main/_view/find\?@'), m::any())
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
            }), m::any(), m::any())->once();
        $connection->shouldReceive('request');

        $storage = self::storage(m::mock(self::couchDb($connection), array('getAllDatabases' => array('fake_db_name'))));
        $storage->ensurePresence(self::yesString());
        $storage->ensurePresence(self::noString());
        $storage->commit();

    }

    public function testMergeExistingStringWithNewOnUpdate()
    {
        $connection = m::mock('Doctrine\\CouchDB\\HTTP\\Client');
        $connection->shouldReceive('request')
            ->with('POST', matchesPattern('@^/fake_db_name/_design/main/_view/find\?@'), m::any())
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
                }), m::any(), m::any())->once();
        $connection->shouldReceive('request');

        $storage = self::storage(m::mock(self::couchDb($connection), array('getAllDatabases' => array('fake_db_name'))));
        $storage->ensurePresence(self::yesString());
        $storage->ensurePresence(self::noString());
        $storage->commit();
    }

    public function testDoesAutoCommitOnEvery100StashedTranslations()
    {
        $connection = m::mock('Doctrine\\CouchDB\\HTTP\\Client');
        $connection->shouldReceive('request')
            ->with('POST', matchesPattern('@^/fake_db_name/_design/main/_view/find\?@'), m::any())
            ->andReturn(new Response(200, array(), array('rows' => array()), true));

        $connection->shouldReceive('request')->with('POST', '/fake_db_name/_bulk_docs', m::any(), m::any(), m::any())->twice();

        $storage = self::storage(
            m::mock(self::couchDb($connection), array('getAllDatabases' => array('fake_db_name')))
        );

        for ($i = 0; $i < 201; $i++) {
            $storage->ensurePresence(self::yesString());
        }

    }


//----------------------------------------------------------------------------------------------------------------------

    private static function storage($couch)
    {
        return new Bulk($couch);
    }

    private static function couchDb($connection)
    {
        return new CouchDBClient($connection, 'fake_db_name');
    }

    private static function yesString()
    {
        return MultiString::create('yes', 'Ja');
    }

    private static function noString()
    {
        return MultiString::create('no', 'Nein');
    }

    private static function noObj()
    {
        return m::mock('', function ($mock) {
                $mock->shouldIgnoreMissing();
                return $mock;
            });
    }
}

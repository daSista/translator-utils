<?php
namespace Translator;

class CouchDbStorageIntegrationTest extends \PHPUnit_Framework_TestCase {

    protected function tearDown() {
        self::db()->dropDatabase('test');
    }

    public function testFetchesTranslationsForAPage() {
        self::storage()->registerTranslation('hello', 'index/index', 'test');
        self::storage()->registerTranslation('hello', 'index/form', 'test');
        self::storage()->registerTranslation('welcome', 'index/form', 'test');

        $this->assertEquals(
            array(
                'hello' => 'hello'
            ),
            self::storage()->readTranslations('index/index', 'test')
        );

        $this->assertEquals(
            array(
                'hello' => 'hello',
                'welcome' => 'welcome'
            ),
            self::storage()->readTranslations('index/form', 'test')
        );
    }

//--------------------------------------------------------------------------------------------------

    private static function storage() {
        return new CouchDbStorage(self::db());
    }

    private static function db() {
        return new \CouchDB\Connection(
            new \CouchDB\Http\LoggingClient(
                new \CouchDB\Http\StreamClient('localhost', 5984)
            ),
            null,
            new \CouchDB\Auth\Cookie('test', 123)
        );
    }
}

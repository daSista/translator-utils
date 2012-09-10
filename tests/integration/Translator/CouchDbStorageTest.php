<?php
namespace Translator;

class CouchDbStorageIntegrationTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {
        self::driver()->createLanguage('test');
    }

    protected function tearDown() {
        self::driver()->deleteLanguage('test');
    }

    public function testFetchesTranslationsForAPage() {
        self::driver()->registerTranslation('hello', 'index/index', 'test');
        self::driver()->registerTranslation('hello', 'index/form', 'test');
        self::driver()->registerTranslation('welcome', 'index/form', 'test');

        $this->assertEquals(
            array(
                'hello' => 'hello'
            ),
            self::driver()->readTranslations('index/index', 'test')
        );

        $this->assertEquals(
            array(
                'hello' => 'hello',
                'welcome' => 'welcome'
            ),
            self::driver()->readTranslations('index/form', 'test')
        );
    }

//--------------------------------------------------------------------------------------------------

    private static function driver() {
        return new CouchDbStorage(
            new \CouchDB\Connection(
                new \CouchDB\Http\LoggingClient(
                    new \CouchDB\Http\StreamClient('localhost', 5984)
                ),
                null,
                new \CouchDB\Auth\Cookie('test', 123)
            )
        );
    }
}

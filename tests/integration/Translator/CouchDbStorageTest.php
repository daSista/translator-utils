<?php
namespace Translator;

class CouchDbStorageIntegrationTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        self::db()->dropDatabase('i18n_en');
    }

    public function testFetchesTranslationsForAPage()
    {
        self::storage()->registerTranslation('hello', 'index/index', 'en');
        self::storage()->registerTranslation('hello', 'index/form', 'en');
        self::storage()->registerTranslation('welcome', 'index/form', 'en');

        $this->assertEquals(
            array(
                'hello' => 'hello'
            ),
            self::storage()->readTranslations('index/index', 'en')
        );

        $this->assertEquals(
            array(
                'hello' => 'hello',
                'welcome' => 'welcome'
            ),
            self::storage()->readTranslations('index/form', 'en')
        );
    }

//--------------------------------------------------------------------------------------------------

    private static function storage()
    {
        return new CouchDbStorage(self::db());
    }

    private static function db()
    {
        return new \CouchDB\Connection(
            new \CouchDB\Http\LoggingClient(
                new \CouchDB\Http\StreamClient('localhost', 5984)
            ),
            null,
            new \CouchDB\Auth\Cookie('test', 123)
        );
    }
}

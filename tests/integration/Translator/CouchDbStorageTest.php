<?php
namespace Translator;

use Doctrine\CouchDB\CouchDBClient;
use Doctrine\CouchDB\HTTP\SocketClient as HttpClient;

class CouchDbStorageIntegrationTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        self::db()->deleteDatabase('i18n_en');
    }

    public function testFetchesTranslationsForAPage()
    {
        self::storage()->registerTranslation('hello', 'index/index');
        self::storage()->registerTranslation('hello', 'index/form');
        self::storage()->registerTranslation('welcome', 'index/form');

        $this->assertEquals(
            array(
                'hello' => 'hello'
            ),
            self::storage()->readTranslations('index/index')
        );

        $this->assertEquals(
            array(
                'hello' => 'hello',
                'welcome' => 'welcome'
            ),
            self::storage()->readTranslations('index/form')
        );
    }

    public function testSurvivesWhenThereAreNoTranslation()
    {
        self::storage()->readTranslations('index/form');
    }

//----------------------------------------------------------------------------------------------------------------------

    private static function storage()
    {
        return new CouchDbStorage(self::db());
    }

    private static function db()
    {
        return new CouchDBClient(new HttpClient(), 'i18n_en');
    }
}

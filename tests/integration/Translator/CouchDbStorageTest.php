<?php
namespace Translator;

use Translator\Test\CouchDbTestCase;

class CouchDbStorageIntegrationTest extends CouchDbTestCase
{
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
}

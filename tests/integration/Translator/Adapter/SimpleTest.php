<?php
namespace Translator\Adapter;

use Translator\Test\CouchDbTestCase;
use Translator\Application;
use Translator\CouchDbStorage;

class SimpleIntegrationTest extends CouchDbTestCase
{
    public function testRegistersTranslation()
    {
        self::adapter()->translate('привет');
        $this->assertEquals(200, self::db()->findDocument(md5('привет'))->status);
    }

//----------------------------------------------------------------------------------------------------------------------

    private static function adapter()
    {
        return new Simple('test_page', new CouchDbStorage(self::db()), Application::TRANSLATE_ON);
    }
}

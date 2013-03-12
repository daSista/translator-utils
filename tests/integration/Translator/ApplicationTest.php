<?php
namespace Translator;

use Translator\Test\CouchDbTestCase;
use Translator\Application;
use Translator\CouchDbStorage;

class SimpleIntegrationTest extends CouchDbTestCase
{
    public function testRegistersTranslation()
    {
        self::app()->translateAdapter('index.php')->translate('привет');
        $this->assertEquals(200, self::db()->findDocument(md5('привет'))->status);
    }

    public function testUsesStringDecoratorByDefault()
    {
        $this->assertEquals(
            '‘608333adc72f545078ede3aad71bfe74’привет’',
            self::app()->translateAdapter('index.php')->translate('привет')
        );
    }

    public function testInjectsAnIframeAtClientSide()
    {
        $this->assertContains('<iframe ', self::app()->injectAtClientSide('index.php', 'ru'));
    }

//----------------------------------------------------------------------------------------------------------------------

    private static function app()
    {
        return new Application('t.example.com', new CouchDbStorage(self::db()), Application::TRANSLATE_ON);
    }
}

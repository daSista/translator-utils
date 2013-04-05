<?php
namespace Translator;

use Translator\Adapter\Simple;
use Translator\Test\CouchDbTestCase;
use Translator\Application;
use Translator\CouchDbStorage;

class ApplicationIntegrationTest extends CouchDbTestCase
{
    public function testUsesStringDecoratorByDefault()
    {
        $this->assertEquals(
            '‘608333adc72f545078ede3aad71bfe74’привет’',
            self::app()->translate('привет')
        );
    }

    public function testInjectsAnIframeAtClientSide()
    {
        $this->assertContains('<iframe ', self::app()->injectAtClientSide('ru_RU'));
    }

//----------------------------------------------------------------------------------------------------------------------

    private static function app()
    {
        $translationsStorage = new CouchDbStorage(self::db());

        return new Application(
            '/translator',
            new Simple($translationsStorage->readTranslations(), Application::TRANSLATE_ON),
            Application::TRANSLATE_ON
        );
    }
}

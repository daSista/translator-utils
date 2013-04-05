<?php
namespace Translator\Adapter;

use Mockery as m;
use Translator\Application;

class SimpleTest extends \PHPUnit_Framework_TestCase
{
    public function testReadsTranslationFromArray()
    {
        $this->assertEquals('привет', self::adapter(null, array('hello' => 'привет'))->translate('hello'));
    }

    public function testUsesStringDecoratorInTranslationMode()
    {
        $decorator = m::mock();
        $decorator->shouldReceive('decorate')->once();
        self::adapter(Application::TRANSLATE_ON, null, $decorator)->translate('foo');
    }

//--------------------------------------------------------------------------------------------------

    private static function adapter($mode = null, $translations = null, $decorator = null)
    {
        return new Simple(
            $translations ? : array(),
            $mode ? : Application::TRANSLATE_OFF,
            $decorator
        );
    }
}

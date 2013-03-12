<?php
namespace Translator\Adapter;

use Mockery as m;

class SimpleTest extends \PHPUnit_Framework_TestCase
{

    public function testTakesATranslationFromDriver()
    {
        $driver = m::mock(array(
            'readTranslations' => array('hello' => 'привет')
        ));

        $this->assertEquals('привет', self::adapter(null, $driver)->translate('hello'));
    }

    public function testUsesStringDecoratorInTranslationMode()
    {
        $decorator = m::mock();
        $decorator->shouldReceive('decorate')->once();
        self::adapter(\Translator\Application::TRANSLATE_ON, null, $decorator)->translate('foo');
    }

    public function testRegistersAStringInCurrentPage()
    {
        $driver = m::mock(array('readTranslations' => array()));
        $driver->shouldReceive('registerTranslation')->with('hello', __FILE__);
        self::adapter(\Translator\Application::TRANSLATE_ON, $driver)->translate('hello');
    }

//--------------------------------------------------------------------------------------------------

    private static function adapter($mode = null, $driver = null, $decorator = null)
    {
        return new Simple(
            __FILE__,
            $driver ? : m::mock(array('readTranslations' => array(), 'registerTranslation' => null)),
            $mode ? : \Translator\Application::TRANSLATE_OFF,
            $decorator
        );
    }
}

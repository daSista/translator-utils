<?php

namespace Translator\Adapter;

use Mockery as m;
use Translator\Application;

class ICUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider messageParametersProvider
     */
    public function testUnderstandsICUFormat($expected, $parameters)
    {
        $this->assertEquals(
            $expected,
            self::adapter()->translate('foundSummary', $parameters)
        );
    }

    public static function messageParametersProvider()
    {
        return array(
            array('He found 1 result in 2 categories', array('male', 1, 2)),
            array('She found 1 result in 2 categories', array('female', 1, 2)),
            array('He found 2 results in 1 category', array('male', 2, 1)),
            array('They found 2 results in 2 categories', array(null, 2, 2)),
            array('They found no results in 2 categories', array(null, 0, 2)),
        );
    }

    /**
     * @dataProvider messageParametersProviderRU
     */
    public function testRussianSentence($expected, $parameters)
    {
        $this->assertEquals(
            $expected,
            self::adapterRU()->translate('foundSummary', $parameters)
        );
    }

    public static function messageParametersProviderRU()
    {
        return array(
            array('Он нашел 1 результат в 2 категориях', array('male', 1, 2)),
            array('Она нашла 1 результат в 2 категориях', array('female', 1, 2)),
            array('Он нашел 2 результата в 1 категории', array('male', 2, 1)),
            array('Они нашли 2 результата в 2 категориях', array(null, 2, 2)),
            array('Они нашли 5 результатов в 10 категориях', array(null, 5, 10)),
            array('Они не нашли ничего в 2 категориях', array(null, 0, 2)),
        );
    }

    public function testIncludesVariableInTranslation()
    {
        $this->assertEquals(
            'His name is John',
            self::adapter(array('name' => 'His name is {0}'))->translate('name', array('John'))
        );
    }

    public function testReturnsDefaultWhenTranslationNotDefined()
    {
        $this->assertEquals('Not defined', self::adapter()->translate('notDefined'));
    }

    public function testDecoratesStringInTranslationMode()
    {
        $decorator = m::mock();
        $decorator->shouldReceive('decorate')->once();
        self::adapter(null, null, $decorator)->translate('foo');

    }

    public function testThrowsAnExceptionIfTheMEssageFormattingFails()
    {
        $icu = new ICU(array('foo' => '{{BAR}'), 'en_US');

        try {
            $icu->translate('foo');
        }
        catch (\Exception $ex) {
            return;
        }

        $this->fail('Exception expected to be thrown');
    }

//----------------------------------------------------------------------------------------------------------------------

    private static function adapter($translations = null, $locale = null, $decorator = null)
    {
        return new ICU($translations ?: array(
            'foundSummary' => <<<ICU
{0, select,
    male {He}
  female {She}
   other {They}
} found {1, plural,
            =0 {no results}
            one {1 result}
          other {# results}
        } in {2, plural,
                  one {1 category}
                other {# categories}
             }
ICU
            ),
            $locale ?: 'en_US',
            is_null($decorator) ? Application::TRANSLATE_OFF : Application::TRANSLATE_ON,
            $decorator
        );
    }

    private static function adapterRU()
    {
        return self::adapter(array(
            'foundSummary' => <<<ICU
{0, select,
    male {Он }
  female {Она }
   other {Они }
}{1, plural,
        =0 {не {0, select,
                    male {нашел }
                  female {нашла }
                   other {нашли }
                }ничего}
        one {{0, select,
                    male {нашел }
                  female {нашла }
                   other {нашли }
                }1 результат}
        few {{0, select,
                    male {нашел }
                  female {нашла }
                   other {нашли }
                }2 результата}
      other {{0, select,
                    male {нашел }
                  female {нашла }
                   other {нашли }
                }# результатов}
    } в {2, plural,
              one {1 категории}
            other {# категориях}
         }
ICU
        ), 'ru_RU');
    }
}

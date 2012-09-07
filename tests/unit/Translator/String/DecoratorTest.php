<?php
namespace Translator\String;

class String_DecoratorTest extends \PHPUnit_Framework_TestCase {

    public function testDecoratesTranslatableString() {
        $this->assertEquals(
            '‘8b1a9953c4611296a827abf8c47804d7’Привет’',
            self::dec()->decorate('Hello', 'Привет')
        );
    }

//--------------------------------------------------------------------------------------------------

    private static function dec() {
        return new Decorator;
    }
}

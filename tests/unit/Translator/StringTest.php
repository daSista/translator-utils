<?php

namespace Translator;

class StringTest extends \PHPUnit_Framework_TestCase
{
    public function testCanBeCreatedWithFactoryMethod()
    {
        $this->assertEquals(
            array(
                '_id' => '9028b14b882e786e2c6bb5ea27e44ec7',
                'key' => 'notEmpty',
                'translation' => 'Should be not empty',
                'namespace' => array('validation', 'error'),
            ),
            self::str()->asDocument()
        );
    }

    public function testGivesKeyAndNamespace()
    {
        $this->assertEquals('notEmpty', self::str()->key());
        $this->assertEquals('validation/error', self::str()->ns());
    }

    public function testIgnoresEmptyNamespace()
    {
        $this->assertEquals(
            String::create(':key', 'Translation'),
            String::create('key', 'Translation')
        );
    }

    public function testConvertsToString()
    {
        $this->assertSame('Should be not empty', strval(self::str()));
    }

    public function testCanBeFoundInHierarchicalArray()
    {
        $this->assertEquals(
            self::str(),
            String::find(
                'validation/error:notEmpty',
                array(
                    'validation' => array(
                        'error' => array(
                            'notEmpty' => 'Should be not empty'
                        )
                    )
                )
            )
        );
    }

//----------------------------------------------------------------------------------------------------------------------

    private static function str()
    {
        return String::create('validation/error:notEmpty', 'Should be not empty');
    }
}

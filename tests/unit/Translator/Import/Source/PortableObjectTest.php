<?php

namespace Translator\Import\Source;

use org\bovigo\vfs\vfsStream;

class PortableObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testIteratesOverTranslations()
    {
        $translations = self::iterate(<<<'PO'
msgid "yes"
msgstr "Yes"

msgid "hello"
msgstr "Hello!"

msgid "long"
msgstr ""
    "This is first line\n"
    "And this is second\n"
    "And \"this\" is \\ quoted"

msgid "quoted"
msgstr "Quoted \\ string \"hehe\""

PO
        );

        $this->assertEquals(
            array(
                'yes' => array('Yes'),
                'hello' => array('Hello!'),
                'long' => array(<<<'TEXT'
This is first line
And this is second
And "this" is \ quoted
TEXT
                ),
                'quoted' => array('Quoted \ string "hehe"')
            ),
            $translations
        );
    }

    public function testCanReadContext()
    {
        $translations = self::iterate(<<<'PO'
msgctxt "general/diverses"
msgid "hello"
msgstr "Hello!"

msgid "long"
msgstr ""
    "This is first line\n"
    "And this is second"
msgctxt "general"
PO
        );

        $this->assertEquals(
            array(
                'general/diverses:hello' => array('Hello!'),
                'general:long' => array(<<<'TEXT'
This is first line
And this is second
TEXT
                )
            ),
            $translations
        );
    }

    public function testIgnoresComments()
    {
        $translations = self::iterate(<<<'PO'

#: lib/error.c:116
msgid "Unknown system error"
msgstr "Error desconegut del sistema"
PO
        );

        $this->assertEquals(
            array(
                'Unknown system error' => array('Error desconegut del sistema'),
            ),
            $translations
        );

    }

    public function testExtractsDescription()
    {
        $translations = self::iterate(<<<'PO'

#. this string needed
#. for special case
msgid "Unknown system error"
msgstr "Error desconegut del sistema"
PO
        );

        $this->assertEquals(
            array(
                'Unknown system error' => array(
                    'Error desconegut del sistema',
                    <<<'DESC'
this string needed
for special case
DESC
                ),
            ),
            $translations
        );
    }

//----------------------------------------------------------------------------------------------------------------------

    private static function iterate($po)
    {
        vfsStream::setup('translations', null, array(
            'language.po' => $po
        ));

        $iterator = new PortableObject();
        $iterator->select(vfsStream::url('translations/language.po'));

        $return = array();
        foreach ($iterator as $key => $value) {
            $return[$key] = $value;
        }
        return $return;
    }
}

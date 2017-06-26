<?php

namespace Translator;

class MultiStringTest extends \PHPUnit_Framework_TestCase
{
    public function testCanBeCreatedWithFactoryMethod()
    {
        $this->assertEquals(
            array(
                'key' => 'notEmpty',
                'translation' => 'Should be not empty',
                'namespace' => array('validation', 'error'),
                'source' => array()
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
            MultiString::create(':key', 'Translation'),
            MultiString::create('key', 'Translation')
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

            MultiString::find(
                'validation/error:notEmpty',
                null,

                array(
                    'validation' => array(
                        'error' => array(
                            'notEmpty' => 'Should be not empty'
                        )
                    )
                ),

                array()
            )
        );
    }

    public function testFindAssignsTheSource()
    {
        $this->assertSame(
            array('/etc/etc'),
            MultiString::find('foo', '/etc/etc', array('foo' => 'BAR'), array())->source()
        );
    }

    public function testDefaultTranslationIfCanNotBeFoundInHierarchicalArrayIfNSLonger()
    {
        $this->assertEquals(
            MultiString::create('validation/error/fatal:notQuiteSimpleKeyToSearch', 'Not quite simple key to search'),

            MultiString::find(
                'validation/error/fatal:notQuiteSimpleKeyToSearch',
                null,

                array(
                    'validation' => array(
                        'error' => array(
                            'notQuiteSimpleKeyToSearch' => 'Should be not empty'
                        )
                    )
                ),

                array()
            )
        );
    }

    public function testDefaultTranslationIfCanNotBeFoundInHierarchicalArrayIfKeyExistsSimilarToNS()
    {
        $this->assertEquals(
            MultiString::create('validation/error:notQuiteSimpleKeyToSearch', 'Not quite simple key to search'),

            MultiString::find(
                'validation/error:notQuiteSimpleKeyToSearch',
                null,

                array(
                    'validation' => array(
                        'error' => 'This is an error'
                    )
                ),

                array()
            )
        );
    }

    public function testDefaultTranslationIfCanNotBeFoundInHierarchicalArrayIfKeyProvidesOtherTranslations()
    {
        $this->assertEquals(
            MultiString::create('validation:error', 'Error'),

            MultiString::find(
                'validation:error',
                null,

                array(
                    'validation' => array(
                        'error' => array(
                            'email' => 'This is an error',
                            'maxLength' => 'This is an error',
                        )
                    )
                ),

                array()
            )
        );
    }

    public function testAllNamespacedKeysReturnsNothingForAnEmptyTranslationsArray()
    {
        $this->assertEquals(array(), MultiString::allNamespacedKeys(array()));
    }

    public function testAllNamespacedKeysWorksForFlatTranslationsArray()
    {
        $this->assertEquals(
            array('foo', 'bar'),
            MultiString::allNamespacedKeys(array('foo' => '1', 'bar' => '2'))
        );
    }

    public function testAllNamespacedKeysWorksForDeeplyNestedTranslationsArray()
    {
        $this->assertEquals(
            array('foo/bar/fiz/buz:moo', 'goo', 'doo:zoo'),

            MultiString::allNamespacedKeys(
                array(
                    'foo' => array('bar' => array('fiz' => array('buz' => array('moo' => 'Му')))),
                    'goo' => 'Гу',
                    'doo' => array('zoo' => 'Зу')
                )
            )
        );
    }

    public function testSupportsDescriptionAndSource()
    {
        $this->assertEquals(
            array(
                'key' => 'notEmpty',
                'translation' => 'Should be not empty',
                'description' => 'This string needed to show validation error',
                'namespace' => array('validation', 'error'),
                'source' => array('/dev/null')
            ),
            self::str('This string needed to show validation error', '/dev/null')->asDocument()
        );
    }

    /**
     * @dataProvider translationPairsDataProvider
     */
    public function testCreatesDefaultTranslationWhenTranslationNotDefined($expectedTranslation, $keyWithNamespace)
    {
        $this->assertEquals($expectedTranslation, strval(MultiString::create($keyWithNamespace, null)));
    }

    public static function translationPairsDataProvider()
    {
        return array(
            array('Not defined', 'notDefined'),
            array('Typically swiss hotels', 'catalog:typicallySwissHotels'),
            array('IBMOffice address', 'catalog:IBMOfficeAddress'),
            array('Text', 'textHTML'),
            array('HTMLtext', 'HTMLtext'),
            array('Super UFOVehicle description', 'superUFOVehicleDescription'),
            array('2 adults in 1 room', '2AdultsIn1Room'),
            array('Children age should be in range 0 to 12 years', 'childrenAgeShouldBeInRange0To12Years'),
            array('Children age should be in range 0-12', 'childrenAgeShouldBeInRange0-12'),
        );
    }

//--------------------------------------------------------------------------------------------------

    private static function str($description = null, $source = null)
    {
        return MultiString::create(
            'validation/error:notEmpty',
            'Should be not empty',
            $description,
            $source
        );
    }
}

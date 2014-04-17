<?php

namespace Translator\SourceCode;

class MustacheViewClauseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider testCases
     */
    public function testExtractsKeyWithNamespaceAndParametersMap($expectedKeyWithNs, $expectedParams, $mustacheClause)
    {
        $clause = new MustacheViewClause($mustacheClause);

        $this->assertSame($expectedKeyWithNs, $clause->keyWithNamespace());
        $this->assertSame($expectedParams, $clause->parameters());
    }

    public static function testCases()
    {
        return array(
            array('', array(), ''),
            array('page', array(), 'page'),
            array('meta/index:keywords', array(), 'meta/index:keywords'),
            array(
                'meta/city:keywords',
                array('city' => 'Lugano', 'region' => 'Ticino'),
                'meta/city:keywords city="Lugano" region="Ticino"'
            ),
            array(
                'xHotelsFound',
                array('NUM' => '9'),
                'xHotelsFound NUM = "9"'
            ),
            array(
                'helloPerson',
                array('NAME' => 'John Doe'),
                'helloPerson NAME = "John Doe"'
            ),
            array(
                'helloPerson',
                array('person.name' => 'John Doe'),
                'helloPerson person.name = "John Doe"'
            ),
            array(
                'names:caffee',
                array('TITLE' => 'Legendary "Titanic"'),
                'names:caffee TITLE = "Legendary "Titanic""'
            ),
            array(
                'greetVisitor',
                array('NAME' => ''),
                'greetVisitor NAME=""'
            ),
            array(
                'greetVisitor',
                array('NAME' => '', 'TITLE' => 'Mr.'),
                'greetVisitor NAME ="" TITLE = "Mr."'
            ),
            array(
                'email/orderConfirmation:dearMrLastName',
                array('NAME_PREFIX' => 'Mr', 'LAST_NAME' => 'Doe'),
                'email/orderConfirmation:dearMrLastName NAME_PREFIX="Mr" LAST_NAME="Doe"'
            ),
        );
    }
}

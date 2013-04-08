<?php
namespace Translator\Storage;

use Translator\Test\CouchDbTestCase;
use Translator\String;

class CouchDbIntegrationTest extends CouchDbTestCase
{
    public function testFetchesTranslationsForANamespace()
    {
        self::fillInStorage();

        $this->assertEquals(
            array(
                'validation/error:notEmpty' => 'Should be not empty',
                'validation/error:emailFormat' => 'Email format is incorrect'
            ),
            self::storage()->mappedTranslations('validation/error')
        );

        $this->assertEquals(
            array(
                'pager:pageXFromY' => 'Page %d from $d',
                'pager:totalAmountOfPages' => 'Total %d page(s)'
            ),
            self::storage()->mappedTranslations('pager')
        );
    }

    public function testSurvivesWhenThereAreNoTranslation()
    {
        self::storage()->mappedTranslations('some/namespace');
    }

    public function testReadsAllTranslations()
    {
        self::fillInStorage();

        $this->assertEquals(
            array(
                'validation:email' => 'Email',
                'validation/error:notEmpty' => 'Should be not empty',
                'validation/error:emailFormat' => 'Email format is incorrect',
                'pager:pageXFromY' => 'Page %d from $d',
                'pager:totalAmountOfPages' => 'Total %d page(s)',
                'yes' => 'Yes'
            ),
            self::storage()->mappedTranslations()
        );
    }

    public function testReadsTranslationsOfSubNamespaces()
    {
        self::fillInStorage();

        $this->assertEquals(
            array(
                'validation:email' => 'Email',
                'validation/error:notEmpty' => 'Should be not empty',
                'validation/error:emailFormat' => 'Email format is incorrect',
            ),
            self::storage()->mappedTranslations('validation')
        );

    }

//----------------------------------------------------------------------------------------------------------------------

    private static function fillInStorage()
    {
        self::storage()->registerString(String::create('validation:email', 'Email'));
        self::storage()->registerString(String::create('validation/error:notEmpty', 'Should be not empty'));
        self::storage()->registerString(String::create('validation/error:emailFormat', 'Email format is incorrect'));
        self::storage()->registerString(String::create('pager:pageXFromY', 'Page %d from $d'));
        self::storage()->registerString(String::create('pager:totalAmountOfPages', 'Total %d page(s)'));
        self::storage()->registerString(String::create('yes', 'Yes'));
    }

    private static function storage()
    {
        return new CouchDb(self::db());
    }
}

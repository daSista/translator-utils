<?php
namespace Translator\Storage;

use Translator\Test\CouchDbTestCase;

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
            self::storage()->readTranslations('validation/error')
        );

        $this->assertEquals(
            array(
                'pager:pageXFromY' => 'Page %d from $d',
                'pager:totalAmountOfPages' => 'Total %d page(s)'
            ),
            self::storage()->readTranslations('pager')
        );
    }

    public function testSurvivesWhenThereAreNoTranslation()
    {
        self::storage()->readTranslations('some/namespace');
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
            self::storage()->readTranslations()
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
            self::storage()->readTranslations('validation')
        );

    }

//----------------------------------------------------------------------------------------------------------------------

    private static function fillInStorage()
    {
        self::storage()->registerTranslation('email', 'Email', 'validation');
        self::storage()->registerTranslation('notEmpty', 'Should be not empty', 'validation/error');
        self::storage()->registerTranslation('emailFormat', 'Email format is incorrect', 'validation/error');
        self::storage()->registerTranslation('pageXFromY', 'Page %d from $d', 'pager');
        self::storage()->registerTranslation('totalAmountOfPages', 'Total %d page(s)', 'pager');
        self::storage()->registerTranslation('yes', 'Yes');
    }

    private static function storage()
    {
        return new CouchDb(self::db());
    }
}

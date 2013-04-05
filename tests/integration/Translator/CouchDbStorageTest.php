<?php
namespace Translator;

use Translator\Test\CouchDbTestCase;

class CouchDbStorageIntegrationTest extends CouchDbTestCase
{
    public function testFetchesTranslationsForANamespace()
    {
        self::storage()->registerTranslation('notEmpty', 'Should be not empty', 'validation/error');
        self::storage()->registerTranslation('emailFormat', 'Email format is incorrect', 'validation/error');
        self::storage()->registerTranslation('pageXFromY', 'Page %d from $d', 'pager');
        self::storage()->registerTranslation('totalAmountOfPages', 'Total %d page(s)', 'pager');

        $this->assertEquals(
            array(
                'notEmpty' => 'Should be not empty',
                'emailFormat' => 'Email format is incorrect'
            ),
            self::storage()->readTranslations('validation/error')
        );

        $this->assertEquals(
            array(
                'pageXFromY' => 'Page %d from $d',
                'totalAmountOfPages' => 'Total %d page(s)'
            ),
            self::storage()->readTranslations('pager')
        );
    }

    public function testSurvivesWhenThereAreNoTranslation()
    {
        self::storage()->readTranslations('some/namespace');
    }

//----------------------------------------------------------------------------------------------------------------------

    private static function storage()
    {
        return new CouchDbStorage(self::db());
    }
}

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

    public function testUpdatesExistingStringPreservingDescription()
    {
        $stringWithDescription = String::create('validation:email', 'Email', 'Validation message');
        $stringWithoutDescription = String::create('validation:email', 'email:');
        self::storage()->setTranslationValue($stringWithDescription);
        self::storage()->setTranslationValue($stringWithoutDescription);

        $doc = self::db()->findDocument($stringWithDescription->id());

        $this->assertEquals('email:', $doc->body['translation']);
        $this->assertEquals('Validation message', $doc->body['description']);
    }

    public function testCanPreserveDatabaseContentWhenStringIsBeingRegistered()
    {
        $string = String::create('now', 'Accurate translation', 'Accurate description');
        self::storage()->setTranslationValue($string);
        self::storage()->ensurePresence(String::create('now', 'Now', 'now word'));

        $this->assertEquals(
            $string->asDocument(),
            array_diff_key(self::db()->findDocument($string->id())->body, array('_rev' => null))
        );
    }

    public function testAppendsMissedContextDescriptionToDatabaseContentWhenStringIsBeingRegistered()
    {
        $string = String::create('now', 'Accurate translation', '');
        self::storage()->setTranslationValue($string);
        self::storage()->ensurePresence(String::create('now', 'Now', 'Context description'));

        $this->assertEquals(
            String::create('now', 'Accurate translation', 'Context description')->asDocument(),
            array_diff_key(self::db()->findDocument($string->id())->body, array('_rev' => null))
        );
    }
}

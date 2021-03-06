<?php
namespace Translator\Storage;

use Translator\Test\CouchDbTestCase;
use Translator\MultiString;

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

    public function testFetchesTranslationsForSeveralNamespaces()
    {
        self::fillInStorage();

        $this->assertEquals(
            array(
                'validation/error:notEmpty' => 'Should be not empty',
                'validation/error:emailFormat' => 'Email format is incorrect',
                'pager:pageXFromY' => 'Page %d from $d',
                'pager:totalAmountOfPages' => 'Total %d page(s)',
            ),
            self::storage()->mappedTranslations(array('validation/error', 'pager'))
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
        $stringWithDescription = MultiString::create('validation:email', 'Email', 'Validation message');
        $stringWithoutDescription = MultiString::create('validation:email', 'email:');
        self::storage()->setTranslationValue($stringWithDescription);
        self::storage()->setTranslationValue($stringWithoutDescription);

        $doc = self::storage()->findDocument($stringWithDescription->hash());

        $this->assertEquals('email:', $doc['translation']);
        $this->assertEquals('Validation message', $doc['description']);
    }

    public function testCanPreserveDatabaseContentWhenStringIsBeingRegistered()
    {
        $string = MultiString::create('now', 'Accurate translation', 'Accurate description');
        self::storage()->setTranslationValue($string);
        self::storage()->ensurePresence(MultiString::create('now', 'Now', 'now word'));

        $this->assertEquals(
            $string->asDocument(),
            array_diff_key(self::storage()->findDocument($string->hash()), array('_id' => null, '_rev' => null))
        );
    }

    public function testAppendsMissedContextDescriptionToDatabaseContentWhenStringIsBeingRegistered()
    {
        $string = MultiString::create('now', 'Accurate translation', '');
        self::storage()->setTranslationValue($string);
        self::storage()->ensurePresence(MultiString::create('now', 'Now', 'Context description'));

        $this->assertEquals(
            MultiString::create('now', 'Accurate translation', 'Context description')->asDocument(),
            array_diff_key(self::storage()->findDocument($string->hash()), array('_id' => null, '_rev' => null))
        );
    }

    public function testEnsurePresenceAccumulatesTheDistinctSources()
    {
        $s = MultiString::create('moo1006:foo', 'bar', null, '/dev/stdin');

        self::storage()->ensurePresence($s);
        self::storage()->ensurePresence($s);
        self::storage()->ensurePresence(MultiString::create('moo1006:foo', 'bar', null, '/dev/null'));

        $doc = self::storage()->findDocument($s->hash());
        $this->assertSame(array('/dev/stdin', '/dev/null'), $doc['source']);
    }

    public function testBulkInsertWorks()
    {
        $bulkStorage = self::bulkStorage();
        $bulkStorage->ensurePresence(MultiString::create('one', 'One'));
        $bulkStorage->ensurePresence(MultiString::create('two', 'Two'));
        $bulkStorage->commit();

        $docs = $this->allButDesignDocument();

        $this->assertCount(2, $docs);
        $this->assertSame('one', $docs[0]['doc']['key']);
        $this->assertSame('two', $docs[1]['doc']['key']);

    }

    public function testBulkUpdateWorks()
    {
        $string = MultiString::create('one', 'One');
        self::storage()->setTranslationValue($string);

        $bulkStorage = self::bulkStorage();
        $bulkStorage->ensurePresence(MultiString::create('one', 'One'));
        $bulkStorage->ensurePresence(MultiString::create('two', 'Two'));
        $bulkStorage->commit();

        $docs = $this->allButDesignDocument();

        $this->assertCount(2, $docs);
        $this->assertSame('one', $docs[0]['doc']['key']);
        $this->assertStringStartsWith('2-', $docs[0]['doc']['_rev'], 'second revision');

    }

//----------------------------------------------------------------------------------------------------------------------

    /**
     * @return array
     */
    private function allButDesignDocument()
    {
        $docs = array_values(
            array_filter(
                self::db()->allDocs()->body['rows'],
                function ($row) {
                    return $row['id'] !== '_design/main';
                }
            )
        );
        return $docs;
    }
}

<?php

namespace Translator\Storage;

use Translator\String;

class PhpArrayTest extends \PHPUnit_Framework_TestCase
{

    public function testFetchesTranslationsForANamespace()
    {
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

    public function testCanMapTranslationsWithoutNamespace()
    {
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

    public function testReadsAllTranslations()
    {
        $this->assertEquals(
            array(
                'validation:email' => 'Email',
                'validation/error:notEmpty' => 'Should be not empty',
                'validation/error:emailFormat' => 'Email format is incorrect',
                'pager:pageXFromY' => 'Page %d from $d',
                'pager:totalAmountOfPages' => 'Total %d page(s)',
            ),
            self::storage()->mappedTranslations()
        );
    }

    public function testReadsTranslationsOfSubNamespaces()
    {
        $this->assertEquals(
            array(
                'validation:email' => 'Email',
                'validation/error:notEmpty' => 'Should be not empty',
                'validation/error:emailFormat' => 'Email format is incorrect',
            ),
            self::storage()->mappedTranslations('validation')
        );
    }

    public function testUpdatesExistingString()
    {
        $string = String::create('validation:email', 'Email for orders');
        $storage = self::storage();
        $storage->setTranslationValue($string);

        $translations = $storage->mappedTranslations('validation');

        $this->assertEquals('Email for orders', $translations['validation:email']);
    }

    public function testCreatesNewTranslationWhenStringIsBeingRegistered()
    {
        $string = String::create('validation:billingAddress', 'Billing address');
        $storage = self::storage();
        $storage->ensurePresence($string);

        $translations = $storage->mappedTranslations('validation');
        $this->assertEquals('Billing address', $translations['validation:billingAddress']);
    }

    public function testCanPreserveTranslationWhenStringIsBeingRegistered()
    {
        $string = String::create('validation:email', 'Email for orders');
        $storage = self::storage();
        $storage->ensurePresence($string);

        $translations = $storage->mappedTranslations('validation');
        $this->assertEquals('Email', $translations['validation:email']);
    }

    private static function storage()
    {
        return new PhpArray(array(
            'validation' => array(
                'email' => 'Email',
            ),
            'validation/error' => array(
                'notEmpty' => 'Should be not empty',
                'emailFormat' => 'Email format is incorrect',
            ),
            'pager' => array(
                'pageXFromY' => 'Page %d from $d',
                'totalAmountOfPages' => 'Total %d page(s)'
            )
        ));
    }
}

<?php

namespace Translator\SourceCode;

use Translator\SourceCode\TranslateIterator\AngularView;
use Translator\String;
use org\bovigo\vfs\vfsStream;
use Mockery as m;
use Translator\Storage\StorageInterface;

class CrawlerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        vfsStream::setup('templates', null, array(
            'order.html' => "<h1> {{ 'title' | i18n}} </h1>",
            'order' => array(
                'details.html' => "<h1> {{ 'order/details:title' | i18n}} </h1>"
            ),
            'view' => array(
                'controller' => array(
                    'index.html' => "<p> {{ 'agb' | i18n}} </p>"
                )
            ),
            'empty' => array()
        ));
    }

    public function testCrawlsThroughTheFilesystemRegisteringTranslations()
    {
        $storage = m::mock();
        $storage->shouldReceive('registerString')
            ->with(equalTo(new String('title', 'The title')), anything())->once();
        $storage->shouldReceive('registerString')
            ->with(equalTo(new String('title', 'Here are the order details', 'order/details')), anything())->once();
        $storage->shouldReceive('registerString')
            ->with(equalTo(new String('agb', 'Terms and conditions')), anything())->once();

        self::crawler($storage)->collectTranslations(array(vfsStream::url('templates')), '.html');
    }

    public function testWorksWellWhenTranslationIsntDefined()
    {
        $storage = m::mock();
        $storage->shouldReceive('registerString')
            ->with(equalTo(new String('title', 'Title', 'order/details')), anything())->once();
        $storage->shouldReceive('registerString');

        self::crawler($storage, array())->collectTranslations(array(vfsStream::url('templates')), '.html');
    }

    public function testWorksWellWhenTranslationKeysAreNotFoundInTheTemplate()
    {
        $storage = m::mock();
        $storage->shouldReceive('registerString')->never();
        self::crawler($storage, array())->collectTranslations(array(vfsStream::url('templates/empty')), '.html');
    }

    public function testFiltersFilesByExtension()
    {
        $storage = m::mock();
        $storage->shouldReceive('registerString')->never();
        self::crawler($storage, array())->collectTranslations(array(vfsStream::url('templates')), '.tmpl');
    }

    public function testTakesMissedContextDescriptionFromGivenArray()
    {
        $storage = m::mock();
        $storage->shouldReceive('registerString')
            ->with(
                equalTo(new String('title', 'Here are the order details', 'order/details', 'H1 title in GUI')),
                anything()
            )
            ->once();
        $storage->shouldReceive('registerString');

        self::crawler($storage, null, self::contextDescriptions())
            ->collectTranslations(array(vfsStream::url('templates')), '.html');

    }

    public function testCrawlerRespectsDatabaseContents()
    {
        $storage = m::mock();
        $storage->shouldReceive('registerString')
            ->with(anything(), StorageInterface::BEHAVIOR_RESPECT_DATABASE_CONTENTS)->atLeast(4);

        self::crawler($storage)->collectTranslations(array(vfsStream::url('templates')), '.html');
    }

//----------------------------------------------------------------------------------------------------------------------

    private static function crawler($storage, $translations = null, $context = null)
    {
        if (is_null($translations)) {
            $translations = self::translations();
        }
        if (is_null($context)) {
            $context = array();
        }

        return new Crawler($storage, new AngularView(), $translations, $context);
    }

    private static function translations()
    {
        return array(
            'title' => 'The title',
            'order' => array(
                'details' => array(
                    'title' => 'Here are the order details'
                )
            ),
            'agb' => 'Terms and conditions'
        );
    }

    private static function contextDescriptions()
    {
        return array(
            'title' => 'Title of site pages',
            'order' => array(
                'details' => array(
                    'title' => 'H1 title in GUI'
                )
            ),
            'agb' => 'Long HTML text'
        );
    }
}

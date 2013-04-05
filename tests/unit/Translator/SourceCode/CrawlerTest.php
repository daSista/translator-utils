<?php

namespace Translator\SourceCode;

use Translator\SourceCode\TranslateIterator\AngularView;
use org\bovigo\vfs\vfsStream;
use Mockery as m;

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
        $storage->shouldReceive('registerTranslation')->with('title', 'The title', null)->once();
        $storage->shouldReceive('registerTranslation')->with('title', 'Order details title', 'order/details')->once();
        $storage->shouldReceive('registerTranslation')->with('agb', 'Terms and conditions', null)->once();

        self::crawler($storage)->collectTranslations(array(vfsStream::url('templates')), self::translations(), '.html');
    }

    public function testWorksWellWhenTranslationIsntDefined()
    {
        $storage = m::mock();
        $storage->shouldReceive('registerTranslation')->with('title', 'order details title', 'order/details')->once();
        $storage->shouldReceive('registerTranslation');

        self::crawler($storage)->collectTranslations(array(vfsStream::url('templates')), array(), '.html');
    }

    public function testWorksWellWhenTranslationsAreNotFound()
    {
        $storage = m::mock();
        $storage->shouldReceive('registerTranslation')->never();
        self::crawler($storage)->collectTranslations(array(vfsStream::url('templates/empty')), array(), '.html');
    }

    public function testFiltersFilesByExtension()
    {
        $storage = m::mock();
        $storage->shouldReceive('registerTranslation')->never();
        self::crawler($storage)->collectTranslations(array(vfsStream::url('templates')), array(), '.tmpl');
    }

//----------------------------------------------------------------------------------------------------------------------

    private static function crawler($storage)
    {
        return new Crawler($storage, new AngularView());
    }

    private static function translations()
    {
        return array(
            'title' => 'The title',
            'order' => array(
                'details' => array(
                    'title' => 'Order details title'
                )
            ),
            'agb' => 'Terms and conditions'
        );
    }
}

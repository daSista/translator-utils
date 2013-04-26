<?php

namespace Translator\SourceCode\TranslateIterator;

class JsonViewTest extends \PHPUnit_Framework_TestCase
{
    public function testCanIterateOverEachTranslationKeys()
    {
        $translations = array();
        $iterator = new JsonView;

        foreach ($iterator->select(__DIR__ . '/data/json-view.json') as $key => $paramNames) {
            $translations[$key] = $paramNames;
        }

        $this->assertEquals(
            array(
                'catalog:typicallySwissHotels' => null,
                'catalog:wellnessAndSpaHotels' => null,
                'catalog:kidsHotels' => null,
                'catalog:swissDeluxeHotels' => null,
                'catalog:affordableHotels' => null,
                'catalog:swissHistoricHotels' => null,
                'catalog:designAndLifestyleHotels' => null,

                'catalog/url:typicallySwissHotels' => null,
                'catalog/url:wellnessAndSpaHotels' => null,
                'catalog/url:kidsHotels' => null,
                'catalog/url:swissDeluxeHotels' => null,
                'catalog/url:affordableHotels' => null,
                'catalog/url:swissHistoricHotels' => null,
                'catalog/url:designAndLifestyleHotels' => null
            ),

            $translations
        );
    }
}

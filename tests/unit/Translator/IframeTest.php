<?php
namespace Translator;

class IframeTest extends \PHPUnit_Framework_TestCase
{
    public function testRepresentsItselfAsAString()
    {
        $this->assertContains('<iframe', strval(self::iframe()));
    }

    public function testLoadsTranslatorApplicationInsideIframe()
    {
        $this->assertContains('src="/translator"', strval(self::iframe('/translator')));
    }

//--------------------------------------------------------------------------------------------------

    private static function iframe($baseUri = '', $pageId = '', $language = '')
    {
        return new Iframe($baseUri, $pageId, $language);
    }
}

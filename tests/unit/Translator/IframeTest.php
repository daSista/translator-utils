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
        $this->assertContains('src="//t.host.name"', strval(self::iframe('t.host.name')));
    }

//--------------------------------------------------------------------------------------------------

    private static function iframe($hostname = '', $pageId = '', $language = '')
    {
        return new Iframe($hostname, $pageId, $language);
    }
}

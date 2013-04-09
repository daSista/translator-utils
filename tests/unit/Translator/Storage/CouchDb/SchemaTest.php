<?php
namespace Translator\Storage\CouchDb;

class SchemaTest extends \PHPUnit_Framework_TestCase
{
    public function testJSCompilationScriptIsAwareOfLocaleCode()
    {
        $schema = new Schema('en_US');
        $design = $schema->getData();
        $this->assertContains(
            'MessageFormat.locale.en',
            $design['lib']['locale']
        );
        $this->assertContains(
            'new MessageFormat(\'en\'',
            $design['lists']['compiled']
        );
    }
}

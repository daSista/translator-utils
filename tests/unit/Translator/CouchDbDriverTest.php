<?php
namespace Translator;

class CouchDbDriverTest extends \PHPUnit_Framework_TestCase {

    public function testReadsTranslationsAsArray() {
        $this->assertInternalType('array', self::driver()->readTranslations('index/index', 'ru'));
    }

//--------------------------------------------------------------------------------------------------

    private static function driver() {
        return new CouchDbDriver();
    }
}

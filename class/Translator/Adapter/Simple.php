<?php
namespace Translator\Adapter;

class Simple {
    const TRANSLATE_ON = 'on';
    const TRANSLATE_OFF = 'off';

    private $translationMode;
    private $pageId;
    private $language;

    private $translations;

    /**
     * @var \Translator\CouchDbDriver
     */
    private $driver;

    /**
     * @var \Translator\String\Decorator
     */
    private $testDecorator;

    public function __construct($translationMode = self::TRANSLATE_OFF,
                                $pageId,
                                $language,
                                $driver,
                                $testDecorator = null
    ) {
        $this->translationMode = $translationMode;
        $this->pageId = $pageId;
        $this->language = $language;
        $this->driver = $driver;
        $this->testDecorator = $testDecorator;
        $this->translations = $this->driver->readTranslations($pageId, $language);
    }

    public function translate($string) {
        $translation = array_key_exists($string, $this->translations) ?
                $this->translations[$string] : $string;

        if ($this->translationMode == self::TRANSLATE_ON) {
            $this->driver->register($string, $this->pageId);
            return $this->decorator()->decorate($string, $translation);
        }

        return $translation;
    }

//--------------------------------------------------------------------------------------------------

    private function decorator() {
        return $this->testDecorator ?: new \Translator\String\Decorator();
    }

}
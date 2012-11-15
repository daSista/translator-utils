<?php
namespace Translator\Adapter;

class Simple {

    private $translationMode;
    private $pageId;
    private $language;

    private $translations;

    /**
     * @var \Translator\CouchDbStorage
     */
    private $driver;

    /**
     * @var \Translator\String\Decorator
     */
    private $testDecorator;

    public function __construct($translationMode = \Translator\Application::TRANSLATE_OFF,
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

        if ($this->translationMode == \Translator\Application::TRANSLATE_ON) {
            $this->driver->registerTranslation($string, $this->pageId, $this->language);
            return $this->decorator()->decorate($string, $translation);
        }

        return $translation;
    }

//--------------------------------------------------------------------------------------------------

    private function decorator() {
        return $this->testDecorator ?: new \Translator\String\Decorator();
    }

}
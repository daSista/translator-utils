<?php
namespace Translator\Adapter;

class Simple
{
    /**
     * @var string
     */
    private $translationMode;

    /**
     * @var string
     */
    private $pageId;

    /**
     * @var array key to value map
     */
    private $translations;

    /**
     * @var \Translator\CouchDbStorage
     */
    private $driver;

    /**
     * @var \Translator\String\Decorator
     */
    private $testDecorator;

    /**
     * @param string $translationMode
     * @param string $pageId
     * @param \Translator\CouchDbStorage $driver
     * @param null|\Translator\String\Decorator $testDecorator
     */
    public function __construct($pageId, $driver, $translationMode = \Translator\Application::TRANSLATE_OFF,
                                $testDecorator = null)
    {
        $this->translationMode = $translationMode;
        $this->pageId = $pageId;
        $this->driver = $driver;
        $this->testDecorator = $testDecorator;
        $this->translations = $this->driver->readTranslations($pageId);
    }

    public function translate($string)
    {
        $translation = array_key_exists($string, $this->translations) ?
                $this->translations[$string] : $string;

        if ($this->translationMode == \Translator\Application::TRANSLATE_ON) {
            $this->driver->registerTranslation($string, $this->pageId);
            return $this->decorator()->decorate($string, $translation);
        }

        return $translation;
    }

//----------------------------------------------------------------------------------------------------------------------

    private function decorator()
    {
        return $this->testDecorator ?: new \Translator\String\Decorator();
    }

}
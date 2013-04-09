<?php

namespace Translator\Adapter;

use Translator\Application;
use Translator\String\Decorator;

class ICU implements AdapterInterface
{
    /**
     * @var string
     */
    private $translationMode;

    private $translations = array();

    private $locale;

    /**
     * @var Decorator
     */
    private $testDecorator;

    public function __construct($translations, $locale, $translationMode = Application::TRANSLATE_OFF,
        $testDecorator = null)
    {
        $this->translations = $translations;
        $this->locale = $locale;
        $this->translationMode = $translationMode;
        $this->testDecorator = $testDecorator;
    }

    public function translate($key, $params = array())
    {
        $translation = array_key_exists($key, $this->translations) ?
            msgfmt_format_message($this->locale, $this->translations[$key], $params)
            :
            $key;

        if ($this->translationMode === Application::TRANSLATE_ON) {
            return $this->decorator()->decorate($key, $translation);
        }

        return $translation;
    }

//----------------------------------------------------------------------------------------------------------------------

    private function decorator()
    {
        return $this->testDecorator ?: new Decorator();
    }

}
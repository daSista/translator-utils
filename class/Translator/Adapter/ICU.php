<?php

namespace Translator\Adapter;

use Translator\Application;
use Translator\String\Decorator;
use Translator\String;

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
        $translation = (
            array_key_exists($key, $this->translations) ?
            $this->format($key, $params) :
            self::defaultTranslation($key)
        );

        if ($this->translationMode === Application::TRANSLATE_ON) {
            return $this->decorator()->decorate($key, $translation);
        }

        return $translation;
    }

//----------------------------------------------------------------------------------------------------------------------

    private function format($key, $params)
    {
        $fmt = msgfmt_create($this->locale, $this->translations[$key]);

        if (!$fmt) {
            throw new \Exception('msgfmt_create() failed');
        }

        $result = msgfmt_format($fmt, $params);

        if (false === $result) {
            throw new \Exception(
                'msgfmt_format() failed: ' .
                msgfmt_get_error_message($fmt) . ' (' . msgfmt_get_error_code($fmt) . ')'
            );
        }

        return $result;
    }

    private function decorator()
    {
        return $this->testDecorator ?: new Decorator();
    }

    private static function defaultTranslation($key) {
        return strval(String::create($key, null));
    }
}
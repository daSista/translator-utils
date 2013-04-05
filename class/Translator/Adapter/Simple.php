<?php
namespace Translator\Adapter;

use Translator\Application;
use Translator\String\Decorator;

class Simple implements AdapterInterface
{
    /**
     * @var string
     */
    private $translationMode;

    /**
     * @var array key to value map
     */
    private $translations;

    /**
     * @var Decorator
     */
    private $testDecorator;

    /**
     * @param array $translations
     * @param string $translationMode
     * @param null|Decorator $testDecorator
     */
    public function __construct($translations, $translationMode = Application::TRANSLATE_OFF,
                                $testDecorator = null)
    {
        $this->translations = $translations;
        $this->translationMode = $translationMode;
        $this->testDecorator = $testDecorator;
    }

    public function translate($key, $params = array())
    {
        $translation = array_key_exists($key, $this->translations) ?
                $this->translations[$key] : $key;

        if ($this->translationMode == Application::TRANSLATE_ON) {
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
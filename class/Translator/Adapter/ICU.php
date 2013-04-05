<?php

namespace Translator\Adapter;

class ICU implements AdapterInterface
{
    private $translations = array();

    private $locale;

    public function __construct($translations, $locale)
    {
        $this->translations = $translations;
        $this->locale = $locale;
    }

    public function translate($key, $params = array())
    {
        if (array_key_exists($key, $this->translations)) {
            return msgfmt_format_message($this->locale, $this->translations[$key], $params);
        }
        return $key;
    }
}
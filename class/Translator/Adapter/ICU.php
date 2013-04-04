<?php

namespace Translator\Adapter;

class ICU 
{
    private $translations = array();

    private $locale;

    public function __construct($translations, $locale)
    {
        $this->translations = $translations;
        $this->locale = $locale;
    }

    public function translate($key, $params)
    {
        $messageFormatter = new \MessageFormatter($this->locale, $this->translations[$key]);

        return $messageFormatter->format($params);
    }
}
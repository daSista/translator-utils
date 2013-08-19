<?php

namespace Translator\Storage;

use Translator\String;

interface StorageInterface
{
    /**
     * @param String $string
     * @return void
     */
    public function ensurePresence($string);

    /**
     * @param String $string
     * @return void
     */
    public function setTranslationValue($string);

    /**
     * @param null|string $namespace
     * @return array key to value map
     */
    public function mappedTranslations($namespace = null);
}
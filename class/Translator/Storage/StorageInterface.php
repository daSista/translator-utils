<?php

namespace Translator\Storage;

interface StorageInterface
{
    /**
     * @param string $key
     * @param string $translation
     * @param null|string $namespace
     * @return void
     */
    public function registerTranslation($key, $translation, $namespace = null);

    /**
     * @param null|string $namespace
     * @return array key to value map
     */
    public function readTranslations($namespace = null);
}
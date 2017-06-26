<?php

namespace Translator\Storage;


class PhpArray implements StorageInterface
{
    private $translations;

    public function __construct($translations)
    {
        $this->translations = $translations;
    }

    /**
     * @param \Translator\MultiString $string
     * @return void
     */
    public function ensurePresence($string)
    {
        if (array_key_exists($string->ns(), $this->translations) &&
            array_key_exists($string->key(), $this->translations[$string->ns()])
        ) {
            return;
        }
        $this->setTranslationValue($string);
    }

    /**
     * @param \Translator\MultiString $string
     * @return void
     */
    public function setTranslationValue($string)
    {
        if (!array_key_exists($string->ns(), $this->translations)) {
            $this->translations[$string->ns()] = array();
        }
        $this->translations[$string->ns()][$string->key()] = strval($string);
    }

    /**
     * @param null|string|array $namespace
     * @return array key to value map
     */
    public function mappedTranslations($namespace = null)
    {
        $namespaces = is_array($namespace) ?
            $namespace : (is_null($namespace) ? array() : array($namespace));

        $resultingMap = array();

        foreach ($this->translations as $ns => $map) {
            if (empty($namespaces) || self::belongsToOneOf($namespaces, $ns)) {
                array_map(
                    function ($key) use ($ns, $map, &$resultingMap) {
                        $resultingMap[$ns . ':' . $key] = $map[$key];
                    },
                    array_keys($map)
                );
            }
        }

        return $resultingMap;
    }

    public function getTranslationsArray()
    {
        return $this->translations;
    }

    private static function belongsToOneOf($namespaces, $ns)
    {
        foreach ($namespaces as $existing) {
            if (preg_match('@^' . preg_quote($existing, '@') . '(/.+|$)@', $ns)) {
                return true;
            }
        }
        return false;
    }
}

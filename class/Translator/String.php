<?php

namespace Translator;

class String
{
    private $key;

    private $translation;

    private $namespace;

    private $description;

    public static function create($keyWithNamespace, $translation, $description = null)
    {
        return new self(
            self::keyPart($keyWithNamespace),
            $translation,
            self::namespacePart($keyWithNamespace),
            $description
        );
    }

    public static function find($keyWithNamespace, array $translations, array $contextDescriptions = array())
    {
        $key = self::keyPart($keyWithNamespace);
        $namespace = self::namespacePart($keyWithNamespace);
        $translation = self::searchInArray($translations, $namespace, $key);
        $description = self::searchInArray($contextDescriptions, $namespace, $key);

        return new self(
            $key,
            $translation ?: self::draftTranslation($keyWithNamespace),
            $namespace,
            $description
        );
    }

    public function __construct($key, $translation, $namespace = null, $description = null)
    {
        $this->key = $key;
        $this->translation = $translation;
        $this->namespace = $namespace;
        $this->description = $description;
    }

    public function id()
    {
        return md5($this->key . $this->namespace);
    }

    public function key()
    {
        return $this->key;
    }

    public function ns()
    {
        return $this->namespace;
    }

    public function asDocument()
    {
        $doc = array(
            '_id' => $this->id(),
            'key' => $this->key,
            'translation' => $this->translation,
            'namespace' => array_filter(explode('/', $this->namespace)) ?: null,
        );
        if (!is_null($this->description)) {
            $doc['description'] = $this->description;
        }
        return $doc;
    }

    public function __toString()
    {
        return $this->translation;
    }

//----------------------------------------------------------------------------------------------------------------------

    private static function keyPart($keyWithNamespace)
    {
        return strrpos($keyWithNamespace, ':') !== false ?
            substr($keyWithNamespace, strrpos($keyWithNamespace, ':') + 1) : $keyWithNamespace;
    }

    private static function namespacePart($keyWithNamespace)
    {
        return strrpos($keyWithNamespace, ':') !== false ?
            substr($keyWithNamespace, 0, strrpos($keyWithNamespace, ':')) : null;
    }

    private static function draftTranslation($keyWithNamespace)
    {
        return str_replace(array('/', ':'), ' ', $keyWithNamespace);
    }

    /**
     * @param array $array
     * @param $namespace
     * @param $key
     * @return null
     */
    private static function searchInArray(array $array, $namespace, $key)
    {
        $readFrom = $array;
        foreach (array_filter(explode('/', $namespace)) as $ns) {
            if (array_key_exists($ns, $readFrom)) {
                $readFrom = $readFrom[$ns];
            } else {
                break;
            }
        }
        $translation = array_key_exists($key, $readFrom) ? $readFrom[$key] : null;
        return $translation;
    }

}
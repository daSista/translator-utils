<?php

namespace Translator;

class String
{
    private $key;

    private $translation;

    private $namespace;

    public static function create($keyWithNamespace, $translation)
    {
        return new self(self::keyPart($keyWithNamespace), $translation, self::namespacePart($keyWithNamespace));
    }

    public static function find($keyWithNamespace, array $translations)
    {
        $key = self::keyPart($keyWithNamespace);
        $namespace = self::namespacePart($keyWithNamespace);

        $readFrom = $translations;
        foreach (array_filter(explode('/', $namespace)) as $ns) {
            if (array_key_exists($ns, $readFrom)) {
                $readFrom = $readFrom[$ns];
            } else {
                break;
            }
        }
        $translation = array_key_exists($key, $readFrom) ? $readFrom[$key] : self::draftTranslation($keyWithNamespace);

        return new self($key, $translation, $namespace);
    }

    public function __construct($key, $translation, $namespace = null)
    {
        $this->key = $key;
        $this->translation = $translation;
        $this->namespace = $namespace;
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
        return array(
            '_id' => $this->id(),
            'key' => $this->key,
            'translation' => $this->translation,
            'namespace' => array_filter(explode('/', $this->namespace)) ?: null,
        );
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
}
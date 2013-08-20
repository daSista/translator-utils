<?php

namespace Translator;

class String
{
    private $key;
    private $translation;
    private $namespace;
    private $description;
    private $source;

    public static function create(
        $keyWithNamespace,
        $translation,
        $description = null,
        $source = null
    )
    {
        $key = self::keyPart($keyWithNamespace);
        $namespace = self::namespacePart($keyWithNamespace);

        return new self(
            $key,
            $translation ?: self::defaultTranslation($key),
            $namespace,
            $description,
            $source
        );
    }

    public static function find(
        $keyWithNamespace,
        $source,
        array $translations,
        array $contextDescriptions
    )
    {
        $key = self::keyPart($keyWithNamespace);
        $namespace = self::namespacePart($keyWithNamespace);
        $translation = self::searchInArray($translations, $namespace, $key);
        $description = self::searchInArray($contextDescriptions, $namespace, $key);

        return new self(
            $key,
            $translation ?: self::defaultTranslation($key),
            $namespace,
            $description,
            $source
        );
    }

    public static function allNamespacedKeys(array $translations)
    {
        return self::allNamespacedKeysInContext($translations, array());
    }

    private function __construct($key, $translation, $namespace, $description, $source)
    {
        $this->key = $key;
        $this->translation = $translation;
        $this->namespace = $namespace;
        $this->description = $description;
        $this->source = $source;
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

    public function source()
    {
        return $this->source;
    }

    public function asDocument()
    {
        $doc = array(
            '_id' => $this->id(),
            'key' => $this->key,
            'translation' => $this->translation,
            'namespace' => array_filter(explode('/', $this->namespace)) ?: null,
        );

        foreach (array('description', 'source') as $prop) {
            if (!is_null($this->$prop)) {
                $doc[$prop] = $this->$prop;
            }
        }

        return $doc;
    }

    public function __toString()
    {
        return $this->translation;
    }

//--------------------------------------------------------------------------------------------------

    private static function allNamespacedKeysInContext($translations, array $currentNamespaceComponents)
    {
        $result = array();

        foreach ($translations as $name => $value) {
            if (is_array($value)) {
                $result = array_merge(
                    $result,

                    self::allNamespacedKeysInContext(
                        $value,
                        array_merge($currentNamespaceComponents, array($name))
                    )
                );
            }
            else {
                $result[] = implode(
                    ':',
                    array_filter(array(implode('/', $currentNamespaceComponents), $name))
                );
            }
        }

        return $result;
    }

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
            if (array_key_exists($ns, $readFrom) && is_array($readFrom[$ns])) {
                $readFrom = $readFrom[$ns];
            } else {
                return null;
            }
        }
        $translation = array_key_exists($key, $readFrom) ? $readFrom[$key] : null;
        return $translation;
    }

    private static function defaultTranslation($key) {
        $string = String::create($key, $key);
        $return = array();
        if (preg_match_all('/(\b|[A-Z0-9]+)[a-z0-9-]+/', $string->key(), $matches)) {
            foreach ($matches[0] as $part) {
                $part = preg_replace('/([0-9-]+)/', ' $1 ', $part);
                $return[] = preg_match('/[A-Z]{2,}/', $part) ? $part : strtolower($part);
            }
            $return[0] = ucfirst($return[0]);
        }
        return count($return) ? trim(preg_replace('/ +/', ' ', join(' ', $return))) : $key;
    }
}
